<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserResource;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Dashboard\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Dashboard\UserService;
use App\Services\ImageStorageService;
use App\Services\LoggingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;
use Tests\TestCase;

class DashboardUserTest extends TestCase
{
    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'hashing.bcrypt.rounds' => 4, 'logging.default' => 'errorlog']);
        DB::purge('sqlite');
        // Mirror the supplied users DDL plus the confirmed avatar column; no audit columns.
        DB::unprepared('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, full_name VARCHAR(255), user_name VARCHAR(255) NOT NULL UNIQUE, phone_number VARCHAR(20) UNIQUE, email_address VARCHAR(255) UNIQUE, password VARCHAR(255), avatar VARCHAR(255), status INTEGER NOT NULL DEFAULT 1 CHECK(status IN (0,1)), type INTEGER NOT NULL DEFAULT 0 CHECK(type IN (0,1,2)), created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, deleted_at TIMESTAMP);
            CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, tokenable_type TEXT, tokenable_id INTEGER, name TEXT, token TEXT UNIQUE, abilities TEXT, expires_at TIMESTAMP, last_used_at TIMESTAMP, created_at TIMESTAMP, updated_at TIMESTAMP);
            CREATE TABLE reviews (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT, place_id INTEGER, rating INTEGER, comment TEXT, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);');
        Storage::fake('documents');
        $this->app->instance(LoggingService::class, new class extends LoggingService
        {
            public function success(string $operation, ?int $userId = null): void {}
        });
        $this->admin = $this->user(['user_name' => 'admin', 'type' => 1]);
        $this->token = $this->admin->createToken('admin')->plainTextToken;
    }

    private function user(array $attributes = []): User
    {
        $id = User::count() + 1;

        return User::create(array_replace(['full_name' => 'User '.$id, 'user_name' => 'user'.$id, 'phone_number' => '079000000'.$id, 'email_address' => 'user'.$id.'@example.com', 'password' => Hash::make('Password123'), 'status' => 1, 'type' => 0, 'avatar' => 'documents/users/existing-'.$id.'.webp'], $attributes));
    }

    private function api(string $method, string $path = '', array $data = [], ?string $token = null, bool $legacy = false): TestResponse
    {
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();

        return $this->call($method, ($legacy ? '/api' : '/api/v1').'/dashboard/user'.$path, $data, [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.($token ?? $this->token)]);
    }

    private function names(User $user): array
    {
        return ['full_name' => $user->full_name, 'user_name' => $user->user_name];
    }

    private function addReview(User $user): int
    {
        return DB::table('reviews')->insertGetId(['user_id' => $user->id, 'place_id' => 10, 'rating' => 5, 'comment' => 'Retain this review', 'created_at' => '2026-11-09 12:00:00']);
    }

    private function failingRevocationService(): UserService
    {
        $repository = new class extends AuthRepository
        {
            public function deleteTokens(User $user): void
            {
                parent::deleteTokens($user);
                throw new RuntimeException('Simulated token revocation failure');
            }
        };

        return new UserService(new UserRepository, new AuthService($repository, $this->app->make(LoggingService::class)), $this->app->make(ImageStorageService::class), $this->app->make(LoggingService::class));
    }

    public function test_index_and_show_have_no_password_or_audit_fields_and_load_review_authors(): void
    {
        $user = $this->user();
        $this->addReview($user);
        $this->api('GET')->assertOk()->assertJsonCount(2, 'data')->assertJsonMissingPath('pagination');
        $response = $this->api('GET', '/'.$user->id)->assertOk()->assertJsonPath('data.reviews.0.user.id', $user->id);
        foreach (['password', 'created_by', 'updated_by'] as $field) {
            $this->assertArrayNotHasKey($field, $response->json('data'));
        }
    }

    public function test_create_ignores_audit_input_uses_defaults_and_hashes_password(): void
    {
        $response = $this->api('POST', '', ['full_name' => 'Created User', 'user_name' => 'new-user', 'phone_number' => '0798888888', 'password' => 'StrongPassword123', 'created_by' => 999, 'updated_by' => 999])->assertCreated();
        $user = User::findOrFail($response->json('data.id'));
        $this->assertEquals(1, $user->status);
        $this->assertEquals(0, $user->type);
        $this->assertTrue(Hash::check('StrongPassword123', $user->password));
        $this->assertArrayNotHasKey('created_by', $response->json('data'));
    }

    public function test_create_without_password_preserves_existing_nullable_account_contract(): void
    {
        $response = $this->api('POST', '', ['full_name' => 'No login', 'user_name' => 'no-login', 'status' => null, 'type' => null])->assertCreated();
        $user = User::findOrFail($response->json('data.id'));
        $this->assertNull($user->password);
        $this->assertEquals(1, $user->status);
        $this->assertEquals(0, $user->type);
    }

    public function test_update_preserves_omitted_values_password_and_existing_tokens(): void
    {
        $user = $this->user(['type' => 2]);
        $hash = $user->password;
        $token = $user->createToken('device')->plainTextToken;
        $before = $user->getAttributes();
        $this->api('POST', '/'.$user->id, array_replace($this->names($user), ['full_name' => 'Renamed', 'created_by' => 999, 'updated_by' => 999]))->assertOk();
        $user->refresh();
        foreach (['user_name', 'phone_number', 'email_address', 'password', 'status', 'type', 'avatar'] as $field) {
            $this->assertEquals($before[$field], $user->$field);
        }
        $this->assertSame($hash, $user->password);
        $this->assertNotNull(PersonalAccessToken::findToken($token));
        $this->api('POST', '/'.$user->id, array_replace($this->names($user), ['password' => '', 'status' => null, 'type' => null]))->assertOk();
        $this->assertSame($hash, $user->refresh()->password);
    }

    public function test_password_change_revokes_only_target_tokens_and_preserves_reviews(): void
    {
        $user = $this->user();
        $id = $this->addReview($user);
        $before = (array) DB::table('reviews')->find($id);
        $token = $user->createToken('device')->plainTextToken;
        $this->api('POST', '/'.$user->id, array_replace($this->names($user), ['password' => 'Replacement123']))->assertOk();
        $this->assertTrue(Hash::check('Replacement123', $user->refresh()->password));
        $this->assertNull(PersonalAccessToken::findToken($token));
        $this->assertNotNull(PersonalAccessToken::findToken($this->token));
        $this->assertSame($before, (array) DB::table('reviews')->find($id));
    }

    public function test_deactivation_preserves_account_reviews_and_avatar_but_revokes_tokens(): void
    {
        $user = $this->user();
        $id = $this->addReview($user);
        $before = (array) DB::table('reviews')->find($id);
        $path = substr($user->avatar, 10);
        Storage::disk('documents')->put($path, 'retained');
        $token = $user->createToken('device')->plainTextToken;
        $this->api('POST', '/'.$user->id, array_replace($this->names($user), ['status' => 0]))->assertOk();
        $this->assertEquals(0, $user->refresh()->status);
        $this->assertNull($user->deleted_at);
        $this->assertNull(PersonalAccessToken::findToken($token));
        $this->assertSame($before, (array) DB::table('reviews')->find($id));
        Storage::disk('documents')->assertExists($path);
    }

    public function test_soft_delete_preserves_user_row_all_reviews_and_avatar(): void
    {
        $user = $this->user();
        $id = $this->addReview($user);
        $before = (array) DB::table('reviews')->find($id);
        $path = substr($user->avatar, 10);
        Storage::disk('documents')->put($path, 'retained');
        $token = $user->createToken('device')->plainTextToken;
        $this->api('DELETE', '/'.$user->id, ['updated_by' => 999])->assertOk()->assertJsonPath('data.id', $user->id);
        $this->assertNotNull($user->refresh()->deleted_at);
        $this->assertEquals(0, $user->status);
        $this->assertSame(2, User::count());
        $this->assertSame($before, (array) DB::table('reviews')->find($id));
        $this->assertNull(PersonalAccessToken::findToken($token));
        Storage::disk('documents')->assertExists($path);
        $this->api('GET')->assertOk()->assertJsonCount(1, 'data');
        $this->api('GET', '/'.$user->id)->assertNotFound();
        $this->api('POST', '/'.$user->id, $this->names($user))->assertNotFound();
        $this->api('DELETE', '/'.$user->id)->assertNotFound();
    }

    public function test_deleted_account_reviews_remain_publicly_resolvable(): void
    {
        $user = $this->user();
        $id = $this->addReview($user);
        $this->api('DELETE', '/'.$user->id)->assertOk();
        $review = Review::with('user')->findOrFail($id);
        $data = ReviewResource::make($review)->resolve();
        $this->assertSame($user->full_name, $data['full_name']);
        $this->assertNotNull($review->user);
    }

    public function test_avatar_creation_and_replacement_use_webp_and_remove_only_old_avatar(): void
    {
        $response = $this->api('POST', '', ['full_name' => 'With avatar', 'user_name' => 'with-avatar', 'avatar' => UploadedFile::fake()->image('original.png')])->assertCreated();
        $user = User::findOrFail($response->json('data.id'));
        $old = substr($user->avatar, 10);
        Storage::disk('documents')->assertExists($old);
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('documents')->get($old))['mime']);
        $this->api('POST', '/'.$user->id, array_replace($this->names($user), ['avatar' => UploadedFile::fake()->image('new.png')]))->assertOk();
        Storage::disk('documents')->assertMissing($old);
        Storage::disk('documents')->assertExists(substr($user->refresh()->avatar, 10));
    }

    public function test_username_phone_email_uniqueness_cannot_be_bypassed_by_request_id(): void
    {
        $a = $this->user();
        $b = $this->user();
        foreach (['user_name', 'phone_number', 'email_address'] as $field) {
            $this->api('POST', '/'.$b->id, array_replace($this->names($b), [$field => $a->$field, 'id' => 999]))->assertUnprocessable();
            $this->api('POST', '', ['full_name' => 'New', 'user_name' => 'new', $field => $a->$field])->assertUnprocessable();
        }
        $this->api('POST', '/'.$b->id, array_replace($this->names($b), ['phone_number' => $b->phone_number, 'email_address' => $b->email_address]))->assertOk();
    }

    public function test_validation_rejects_unsafe_passwords_and_invalid_avatar_and_inputs(): void
    {
        $base = ['full_name' => 'New', 'user_name' => 'new'];
        foreach ([['password' => 'short'], ['password' => str_repeat('ع', 40)], ['phone_number' => 'invalid'], ['type' => 9], ['status' => 9], ['email_address' => 'bad-email'], ['avatar' => UploadedFile::fake()->create('fake.png', 10, 'image/png')], ['avatar' => UploadedFile::fake()->image('big.png')->size(5121)], ['avatar' => UploadedFile::fake()->image('wide.png', 4097, 2)]] as $data) {
            $this->api('POST', '', array_replace($base, $data))->assertUnprocessable()->assertJsonStructure(['error' => ['details']]);
        }
        $this->api('POST')->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['full_name' => ['ar', 'en']]]]);
        $this->api('GET', '/999')->assertNotFound();
        $this->api('DELETE', '/999')->assertNotFound();
    }

    public function test_staff_and_customers_cannot_access_any_user_operation_or_legacy_alias(): void
    {
        $target = $this->user();
        foreach ([0, 2] as $type) {
            $actor = $this->user(['type' => $type]);
            $token = $actor->createToken('test')->plainTextToken;
            foreach ([['GET', ''], ['GET', '/'.$target->id], ['POST', ''], ['POST', '/'.$target->id], ['DELETE', '/'.$target->id]] as [$method, $path]) {
                $this->api($method, $path, [], $token)->assertForbidden();
                $this->api($method, $path, [], $token, true)->assertForbidden();
            }
        }
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
        $this->call('GET', '/api/v1/dashboard/user')->assertUnauthorized();
    }

    public function test_failed_token_revocation_rolls_back_soft_delete_and_preserves_related_content(): void
    {
        $user = $this->user();
        $id = $this->addReview($user);
        $before = (array) DB::table('reviews')->find($id);
        $token = $user->createToken('device')->plainTextToken;
        try {
            $this->failingRevocationService()->delete($user, (int) $this->admin->id);
            $this->fail('Expected revocation failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated token revocation failure', $e->getMessage());
        }
        $this->assertNull($user->refresh()->deleted_at);
        $this->assertEquals(1, $user->status);
        $this->assertNotNull(PersonalAccessToken::findToken($token));
        $this->assertSame($before, (array) DB::table('reviews')->find($id));
    }

    public function test_failed_credential_update_restores_hash_token_and_old_avatar_and_cleans_new_file(): void
    {
        $user = $this->user();
        $hash = $user->password;
        $path = substr($user->avatar, 10);
        Storage::disk('documents')->put($path, 'retained');
        $token = $user->createToken('device')->plainTextToken;
        try {
            $this->failingRevocationService()->update(['password' => 'Replacement123'], $user, (int) $this->admin->id, UploadedFile::fake()->image('new.png'));
            $this->fail('Expected revocation failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated token revocation failure', $e->getMessage());
        }
        $this->assertSame($hash, $user->refresh()->password);
        $this->assertNotNull(PersonalAccessToken::findToken($token));
        $this->assertSame('documents/'.$path, $user->avatar);
        Storage::disk('documents')->assertExists($path);
        $this->assertCount(1, Storage::disk('documents')->allFiles());
    }

    public function test_list_does_not_select_passwords_or_issue_per_user_relationship_queries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = $this->user();
            $this->addReview($user);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $users = (new UserRepository)->getAll();
            $response = ResponseHelper::success(UserResource::collection($users));
            $this->assertSame(200, $response->getStatusCode());
            $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
            foreach ($users as $user) {
                $this->assertArrayNotHasKey('password', $user->getAttributes());
            }
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_failed_create_cleans_orphan_avatar(): void
    {
        $repository = new class extends UserRepository
        {
            public function create(array $attributes): User
            {
                throw new RuntimeException('Simulated create failure');
            }
        };
        $service = new UserService($repository, $this->app->make(AuthService::class), $this->app->make(ImageStorageService::class), $this->app->make(LoggingService::class));
        try {
            $service->create(['full_name' => 'New', 'user_name' => 'new'], (int) $this->admin->id, UploadedFile::fake()->image('new.png'));
            $this->fail('Expected create failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated create failure', $e->getMessage());
        }
        $this->assertCount(0, Storage::disk('documents')->allFiles());
    }
}
