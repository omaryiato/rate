<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\ApiRateLimiters;
use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Services\Auth\AuthService;
use App\Services\LoggingService;
use DateTimeInterface;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;
use Tests\TestCase;

class MobileApiSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Fixtures are created exclusively in memory; no migrations or real schema changes.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'cache.limiter' => 'array', 'hashing.bcrypt.rounds' => 4, 'app.debug' => true, 'logging.default' => 'errorlog']);
        DB::purge('sqlite');
        $this->app->forgetInstance(CacheRateLimiter::class);
        RateLimiter::clearResolvedInstance(CacheRateLimiter::class);
        ApiRateLimiters::register();
        $this->app->instance(LoggingService::class, new class extends LoggingService
        {
            public function success(string $operation, ?int $userId = null): void {}
        });
        DB::unprepared('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, full_name TEXT, user_name TEXT NOT NULL UNIQUE, phone_number TEXT UNIQUE, email_address TEXT UNIQUE, password TEXT, status INTEGER NOT NULL DEFAULT 1, type INTEGER NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);
            CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, tokenable_type TEXT, tokenable_id INTEGER, name TEXT, token TEXT UNIQUE, abilities TEXT, last_used_at TIMESTAMP, expires_at TIMESTAMP, created_at TIMESTAMP, updated_at TIMESTAMP);
            CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT, en_name TEXT, ar_name TEXT, status INTEGER DEFAULT 1);
            CREATE TABLE places (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, en_name TEXT, ar_name TEXT, status INTEGER DEFAULT 1);
            CREATE TABLE reviews (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, place_id INTEGER, comment TEXT, rating INTEGER, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);
            CREATE TABLE feedback (id INTEGER PRIMARY KEY AUTOINCREMENT, subject TEXT, description TEXT, created_at TIMESTAMP);');
    }

    private function user(int $type = 0, string $phone = '0791234567'): User
    {
        return User::create(['full_name' => 'Test Customer', 'user_name' => $phone, 'phone_number' => $phone, 'password' => Hash::make('StrongPassword123'), 'status' => 1, 'type' => $type]);
    }

    private function api(string $method, string $path, array $data = [], ?string $token = null): TestResponse
    {
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();

        return $this->json($method, '/api/'.$path, $data, $token ? ['Authorization' => 'Bearer '.$token] : []);
    }

    private function place(): int
    {
        $category = DB::table('category')->insertGetId(['en_name' => 'Food', 'ar_name' => 'Food', 'status' => 1]);

        return DB::table('places')->insertGetId(['category_id' => $category, 'en_name' => 'Cafe', 'ar_name' => 'Cafe', 'status' => 1]);
    }

    public function test_signup_keeps_customer_defaults_hashes_password_and_preserves_duplicate_error_code(): void
    {
        $data = ['full_name' => 'New Customer', 'phone_number' => '0791234567', 'password' => 'StrongPassword123', 'type' => 1, 'status' => 0];
        $response = $this->api('POST', 'v1/signup', $data);
        $response->assertCreated()->assertJsonPath('data.type', 0)->assertJsonPath('data.status', 1);
        $user = User::firstOrFail();
        $this->assertSame($data['phone_number'], $user->user_name);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->api('POST', 'signup', $data)->assertConflict()->assertJsonPath('error.code', 'ACCOUNT_ALREADY_EXISTS');
    }

    public function test_single_session_login_revokes_previous_device_and_expires_after_ten_hours(): void
    {
        $user = $this->user();
        $old = $user->createToken('old')->plainTextToken;
        $response = $this->api('POST', 'v1/login', ['phone_number' => $user->phone_number, 'password' => 'StrongPassword123']);
        $response->assertOk()->assertJsonPath('data.user.type', 0)->assertJsonPath('data.token_type', 'Bearer');
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
        $this->assertNull(PersonalAccessToken::findToken($old));
        $token = PersonalAccessToken::findToken($response->json('data.token'));
        $this->assertNotNull($token);
        $this->assertEqualsWithDelta(now()->addHours(10)->timestamp, $token->expires_at->timestamp, 3);
        $this->api('GET', 'v1/me', [], $old)->assertUnauthorized();
        $this->api('GET', 'v1/me', [], $response->json('data.token'))->assertOk();
    }

    public function test_customer_can_inspect_session_and_logout_without_dashboard_permissions(): void
    {
        $user = $this->user();
        $token = $user->createToken('device')->plainTextToken;
        $this->api('GET', 'v1/refresh', [], $token)->assertOk()->assertJsonPath('data.id', $user->id);
        $this->api('POST', 'v1/logout', [], $token)->assertOk();
        $this->api('GET', 'v1/me', [], $token)->assertUnauthorized();
    }

    public function test_account_deactivation_or_deletion_blocks_existing_tokens(): void
    {
        $user = $this->user();
        $token = $user->createToken('device')->plainTextToken;
        $user->update(['status' => 0]);
        $this->api('GET', 'v1/me', [], $token)->assertForbidden()->assertJsonPath('error.code', 'ACCOUNT_INACTIVE');
        $user->forceFill(['status' => 1, 'deleted_at' => now()])->save();
        $this->api('GET', 'v1/me', [], $token)->assertForbidden();
    }

    public function test_expired_tokens_are_rejected(): void
    {
        $token = $this->user()->createToken('expired', ['*'], now()->subMinute())->plainTextToken;
        $this->api('GET', 'v1/me', [], $token)->assertUnauthorized();
    }

    public function test_dashboard_preserves_admin_staff_access_and_rejects_customers(): void
    {
        foreach ([0 => 403, 1 => 200, 2 => 200] as $type => $status) {
            $token = $this->user($type, '079123456'.$type)->createToken('device')->plainTextToken;
            $this->api('GET', 'v1/dashboard/category', [], $token)->assertStatus($status);
        }
    }

    public function test_login_phone_limit_is_shared_across_legacy_and_versioned_routes(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->api('POST', $i % 2 ? 'login' : 'v1/login', ['phone_number' => '0799999999', 'password' => 'WrongPassword'])->assertUnauthorized();
        }
        $this->api('POST', 'v1/login', ['phone_number' => '0799999999', 'password' => 'WrongPassword'])
            ->assertStatus(429)->assertHeader('Retry-After')->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
    }

    public function test_login_ip_limit_prevents_bypass_by_changing_phone_numbers(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->api('POST', 'v1/login', ['phone_number' => (string) (7900000000 + $i), 'password' => 'WrongPassword'])->assertUnauthorized();
        }
        $this->api('POST', 'v1/login', ['phone_number' => '0798888888', 'password' => 'WrongPassword'])->assertStatus(429);
    }

    public function test_signup_and_feedback_have_independent_rate_limits(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->api('POST', 'v1/signup')->assertUnprocessable();
            $this->api('POST', 'v1/client/feedback')->assertUnprocessable();
        }
        $this->api('POST', 'signup')->assertStatus(429);
        $this->api('POST', 'client/feedback')->assertStatus(429);
    }

    public function test_review_requires_authentication_and_ignores_forged_user_identity(): void
    {
        $place = $this->place();
        $user = $this->user();
        $other = $this->user(0, '0799999999');
        $data = ['place_id' => $place, 'rating' => 5, 'user_id' => $other->id];
        $this->api('POST', 'v1/client/review', $data)->assertUnauthorized();
        $token = $user->createToken('device')->plainTextToken;
        $response = $this->api('POST', 'v1/client/review', $data, $token)->assertCreated();
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'place_id' => $place]);
        $this->assertDatabaseMissing('reviews', ['user_id' => $other->id]);
        $this->assertStringNotContainsString($user->phone_number, $response->getContent());
        $this->assertArrayNotHasKey('user_name', $response->json('data'));
    }

    public function test_invalid_client_inputs_return_bilingual_details(): void
    {
        $token = $this->user()->createToken('device')->plainTextToken;
        $this->api('POST', 'v1/client/review', ['place_id' => 999, 'rating' => 6], $token)
            ->assertUnprocessable()->assertJsonStructure(['error' => ['code', 'details' => ['place_id' => ['ar', 'en']]]]);
        $this->api('GET', 'v1/client/index?per_page=51')->assertOk()->assertJsonMissingPath('pagination');
        $this->api('POST', 'v1/client/feedback', ['subject' => [], 'description' => []])->assertUnprocessable();
    }

    public function test_feedback_accepts_validated_text_and_returns_a_single_resource(): void
    {
        $response = $this->api('POST', 'v1/client/feedback', ['subject' => 'Suggestion', 'description' => 'Please add more places', 'created_at' => '1900-01-01']);
        $response->assertCreated()->assertJsonStructure(['data' => ['id']]);
        $this->assertDatabaseHas('feedback', ['id' => $response->json('data.id'), 'subject' => 'Suggestion']);
    }

    public function test_public_browsing_returns_full_private_lists_without_lazy_loaded_queries(): void
    {
        $place = $this->place();
        $user = $this->user();
        for ($i = 0; $i < 7; $i++) {
            DB::table('reviews')->insert(['user_id' => $user->id, 'place_id' => $place, 'rating' => 5]);
        }
        Model::preventLazyLoading();
        try {
            $this->api('GET', 'v1/client/index?per_page=1')->assertOk()->assertJsonMissingPath('pagination')->assertJsonCount(7, 'data.0.places.0.place_reviews');
            $response = $this->api('GET', 'v1/client/places/1?per_page=1')->assertOk()->assertJsonCount(7, 'data.0.place_reviews');
            $this->assertStringNotContainsString($user->phone_number, $response->getContent());
            $this->api('GET', 'v1/client/places/'.$place.'/reviews?per_page=2')->assertOk()->assertJsonCount(7, 'data')->assertJsonMissingPath('pagination');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_login_rate_limit_expires_after_one_minute(): void
    {
        $data = ['phone_number' => '0799999999', 'password' => 'WrongPassword'];
        for ($i = 0; $i < 5; $i++) {
            $this->api('POST', 'v1/login', $data)->assertUnauthorized();
        }
        $this->api('POST', 'v1/login', $data)->assertStatus(429);
        $this->travel(61)->seconds();
        $this->api('POST', 'v1/login', $data)->assertUnauthorized();
    }

    public function test_review_write_limit_is_per_user_and_cannot_be_bypassed_by_aliases(): void
    {
        $place = $this->place();
        $token = $this->user()->createToken('device')->plainTextToken;
        for ($i = 0; $i < 10; $i++) {
            $this->api('POST', $i % 2 ? 'client/review' : 'v1/client/review', ['place_id' => $place, 'rating' => 5], $token)->assertCreated();
        }
        $this->api('POST', 'v1/client/review', ['place_id' => $place, 'rating' => 5], $token)->assertStatus(429);
        $this->assertSame(10, DB::table('reviews')->count());
    }

    public function test_home_returns_all_places_and_reviews_with_a_constant_query_count(): void
    {
        $user = $this->user();
        $category = DB::table('category')->insertGetId(['status' => 1]);
        for ($i = 0; $i < 12; $i++) {
            $place = DB::table('places')->insertGetId(['category_id' => $category, 'status' => 1]);
            for ($j = 0; $j < 7; $j++) {
                DB::table('reviews')->insert(['place_id' => $place, 'user_id' => $user->id, 'rating' => 5]);
            }
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->api('GET', 'v1/client/index')->assertOk()->assertJsonCount(12, 'data.0.places')->assertJsonMissingPath('pagination');
        $this->assertLessThanOrEqual(6, count(DB::getQueryLog()));
        foreach ($response->json('data.0.places') as $place) {
            $this->assertCount(7, $place['place_reviews']);
        }
        DB::disableQueryLog();
    }

    public function test_logging_failure_and_fallback_failure_do_not_interrupt_the_operation(): void
    {
        Log::shouldReceive('build')->once()->andThrow(new RuntimeException('Logging unavailable'));
        Log::shouldReceive('warning')->once()->andThrow(new RuntimeException('Fallback unavailable'));
        (new LoggingService)->success('CreateFeedback');
        $this->addToAssertionCount(1);
    }

    public function test_token_issuance_failure_restores_the_previous_session(): void
    {
        $user = $this->user();
        $token = $user->createToken('device')->plainTextToken;
        $repository = new class extends AuthRepository
        {
            public function createToken(User $user, DateTimeInterface $expiresAt): NewAccessToken
            {
                throw new RuntimeException('Simulated token failure');
            }
        };
        try {
            (new AuthService($repository, $this->app->make(LoggingService::class)))->login(['phone_number' => $user->phone_number, 'password' => 'StrongPassword123']);
            $this->fail('Expected a token issuance failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated token failure', $exception->getMessage());
        }
        $this->assertNotNull(PersonalAccessToken::findToken($token));
    }

    public function test_unexpected_errors_and_unknown_routes_do_not_expose_internal_details(): void
    {
        DB::statement('DROP TABLE users');
        $response = $this->api('POST', 'v1/login', ['phone_number' => '0791234567', 'password' => 'StrongPassword123']);
        $response->assertStatus(500)->assertJsonPath('error.code', 'INTERNAL_ERROR');
        $this->assertArrayNotHasKey('trace', $response->json());
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->api('GET', 'v1/unknown')->assertNotFound()->assertJsonStructure(['status', 'message' => ['ar', 'en'], 'error']);
    }
}
