<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Place;
use App\Models\User;
use App\Repositories\Dashboard\CategoryRepository;
use App\Repositories\Dashboard\PlaceRepository;
use App\Services\Dashboard\CategoryService;
use App\Services\Dashboard\PlaceService;
use App\Services\ImageStorageService;
use App\Services\LoggingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Tests\TestCase;

class DashboardCategoryPlaceTest extends TestCase
{
    private User $actor;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        // Explicit in-memory fixtures; never run migrations or use the configured production DB.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'hashing.bcrypt.rounds' => 4, 'logging.default' => 'errorlog']);
        DB::purge('sqlite');
        DB::unprepared('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, full_name TEXT, user_name TEXT UNIQUE, phone_number TEXT UNIQUE, password TEXT, status INTEGER DEFAULT 1, type INTEGER DEFAULT 0, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);
            CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, tokenable_type TEXT, tokenable_id INTEGER, name TEXT, token TEXT UNIQUE, abilities TEXT, expires_at TIMESTAMP, last_used_at TIMESTAMP, created_at TIMESTAMP, updated_at TIMESTAMP);
            CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT, en_name TEXT UNIQUE, ar_name TEXT UNIQUE, description TEXT, slug TEXT UNIQUE, image TEXT, icone TEXT, sort INTEGER DEFAULT 0, status INTEGER DEFAULT 1, created_by INTEGER, updated_by INTEGER, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);
            CREATE TABLE places (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, en_name TEXT, ar_name TEXT, en_description TEXT, ar_description TEXT, slug TEXT, website_url TEXT, image TEXT, status INTEGER DEFAULT 1, created_by INTEGER, updated_by INTEGER, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP, UNIQUE(category_id, en_name), UNIQUE(category_id, ar_name));
            CREATE TABLE reviews (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, place_id INTEGER, rating INTEGER, comment TEXT, created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP);');
        Storage::fake('documents');
        $this->app->instance(LoggingService::class, new class extends LoggingService
        {
            public function success(string $operation, ?int $userId = null): void {}
        });
        $this->actor = User::create(['full_name' => 'Admin', 'user_name' => 'admin', 'password' => Hash::make('Password123'), 'status' => 1, 'type' => 1]);
        $this->token = $this->actor->createToken('test')->plainTextToken;
    }

    private function api(string $method, string $path, array $data = [], ?string $token = null): TestResponse
    {
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
        $this->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer '.($token ?? $this->token)]);

        return $this->call($method, '/api/v1/dashboard/'.$path, $data, [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.($token ?? $this->token)]);
    }

    private function category(array $attributes = []): Category
    {
        $next = Category::count() + 1;

        return Category::create(array_replace(['en_name' => 'Category '.$next, 'ar_name' => 'Arabic '.$next, 'description' => 'Original description', 'image' => 'documents/categories/old.webp', 'slug' => 'category-'.$next, 'icone' => 'icon', 'sort' => 3, 'status' => 1, 'created_by' => $this->actor->id, 'updated_by' => $this->actor->id], $attributes));
    }

    private function place(Category $category, array $attributes = []): Place
    {
        $next = Place::count() + 1;

        return Place::create(array_replace(['category_id' => $category->id, 'en_name' => 'Place '.$next, 'ar_name' => 'Arabic Place '.$next, 'en_description' => 'Original description', 'ar_description' => 'Original Arabic description', 'slug' => 'place-'.$next, 'website_url' => 'https://example.com', 'image' => 'documents/places/old.webp', 'status' => 1, 'created_by' => $this->actor->id, 'updated_by' => $this->actor->id], $attributes));
    }

    public function test_category_index_and_show_load_valid_relationships_without_pagination(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $this->api('GET', 'category')->assertOk()->assertJsonPath('data.0.id', $category->id)->assertJsonPath('data.0.places.0.id', $place->id)->assertJsonMissingPath('pagination');
        $this->api('GET', 'category/'.$category->id)->assertOk()->assertJsonPath('data.id', $category->id);
    }

    public function test_place_index_and_show_load_category_and_review_authors(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        DB::table('reviews')->insert(['user_id' => $this->actor->id, 'place_id' => $place->id, 'rating' => 5]);
        $this->api('GET', 'place')->assertOk()->assertJsonPath('data.0.category_en_name', $category->en_name)->assertJsonPath('data.0.place_reviews.0.user.id', $this->actor->id);
        $this->api('GET', 'place/'.$place->id)->assertOk()->assertJsonPath('data.id', $place->id);
    }

    public function test_create_category_uses_authenticated_actor_and_encodes_image(): void
    {
        $response = $this->api('POST', 'category', ['en_name' => 'New', 'ar_name' => 'New Arabic', 'image' => UploadedFile::fake()->image('unsafe name.png', 20, 20), 'created_by' => 999, 'updated_by' => 999])->assertCreated();
        $category = Category::findOrFail($response->json('data.id'));
        $this->assertEquals($this->actor->id, $category->created_by);
        $this->assertEquals($this->actor->id, $category->updated_by);
        $this->assertStringStartsWith('documents/categories/', $category->image);
        Storage::disk('documents')->assertExists(substr($category->image, 10));
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('documents')->get(substr($category->image, 10)))['mime']);
    }

    public function test_create_place_saves_real_inputs_and_authenticated_audit_fields(): void
    {
        $category = $this->category();
        $response = $this->api('POST', 'place', ['category_id' => $category->id, 'en_name' => 'New Place', 'ar_name' => 'New Arabic Place', 'image' => UploadedFile::fake()->image('place.png'), 'created_by' => 999, 'updated_by' => 999])->assertCreated();
        $place = Place::findOrFail($response->json('data.id'));
        $this->assertSame('New Place', $place->en_name);
        $this->assertEquals($category->id, $place->category_id);
        $this->assertEquals($this->actor->id, $place->created_by);
        $this->assertEquals($this->actor->id, $place->updated_by);
        Storage::disk('documents')->assertExists(substr($place->image, 10));
    }

    public function test_update_without_upload_preserves_creator_image_and_omitted_fields(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $second = User::create(['user_name' => 'second', 'type' => 1, 'status' => 1]);
        $token = $second->createToken('test')->plainTextToken;
        $this->api('POST', 'category/'.$category->id, ['en_name' => 'Renamed', 'ar_name' => $category->ar_name, 'created_by' => 999, 'updated_by' => 999], $token)->assertOk();
        $category->refresh();
        $this->assertEquals($this->actor->id, $category->created_by);
        $this->assertEquals($second->id, $category->updated_by);
        $this->assertSame('documents/categories/old.webp', $category->image);
        $this->assertSame('Original description', $category->description);
        $this->assertSame('category-1', $category->slug);
        $this->assertSame('icon', $category->icone);
        $this->api('POST', 'place/'.$place->id, ['category_id' => $category->id, 'en_name' => 'Renamed Place', 'ar_name' => $place->ar_name, 'created_by' => 999, 'updated_by' => 999], $token)->assertOk();
        $place->refresh();
        $this->assertEquals($this->actor->id, $place->created_by);
        $this->assertEquals($second->id, $place->updated_by);
        $this->assertSame('documents/places/old.webp', $place->image);
        $this->assertSame('https://example.com', $place->website_url);
        $this->assertSame('Original description', $place->en_description);
    }

    public function test_image_replacement_deletes_old_image_after_successful_save(): void
    {
        foreach (['category', 'place'] as $resource) {
            $category = $this->category();
            $model = $resource === 'category' ? $category : $this->place($category);
            $old = substr($model->image, 10);
            Storage::disk('documents')->put($old, 'old');
            $data = ['en_name' => $model->en_name, 'ar_name' => $model->ar_name, 'image' => UploadedFile::fake()->image('replacement.png')];
            if ($resource === 'place') {
                $data['category_id'] = $category->id;
            }
            $this->api('POST', $resource.'/'.$model->id, $data)->assertOk();
            $model->refresh();
            Storage::disk('documents')->assertMissing($old);
            Storage::disk('documents')->assertExists(substr($model->image, 10));
        }
    }

    public function test_unique_update_rejects_other_records_even_with_forged_id(): void
    {
        $first = $this->category();
        $second = $this->category();
        $this->api('POST', 'category/'.$second->id, ['en_name' => $first->en_name, 'ar_name' => $second->ar_name, 'id' => 999])->assertUnprocessable();
        $a = $this->place($first);
        $b = $this->place($first);
        $this->api('POST', 'place/'.$b->id, ['category_id' => $first->id, 'en_name' => $a->en_name, 'ar_name' => $b->ar_name, 'id' => 999])->assertUnprocessable();
    }

    public function test_delete_category_soft_deletes_only_its_designated_places(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $other = $this->place($this->category());
        $this->api('DELETE', 'category/'.$category->id, ['updated_by' => 999])->assertOk()->assertJsonPath('data.id', $category->id);
        $this->assertNotNull($category->refresh()->deleted_at);
        $this->assertNotNull($place->refresh()->deleted_at);
        $this->assertEquals($this->actor->id, $place->updated_by);
        $this->assertSame(0, $place->status);
        $this->assertNull($other->refresh()->deleted_at);
        $this->api('GET', 'category/'.$category->id)->assertNotFound();
    }

    public function test_delete_place_returns_resource_and_preserves_other_places(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $other = $this->place($category);
        $this->api('DELETE', 'place/'.$place->id, ['updated_by' => 999])->assertOk()->assertJsonPath('data.id', $place->id);
        $this->assertNotNull($place->refresh()->deleted_at);
        $this->assertEquals($this->actor->id, $place->updated_by);
        $this->assertNull($other->refresh()->deleted_at);
        $this->api('GET', 'place/'.$place->id)->assertNotFound();
    }

    public function test_deactivation_is_atomic_and_reactivation_does_not_restore_children(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $data = ['en_name' => $category->en_name, 'ar_name' => $category->ar_name, 'status' => 0];
        $this->api('POST', 'category/'.$category->id, $data)->assertOk();
        $this->assertSame(0, $place->refresh()->status);
        $data['status'] = 1;
        $this->api('POST', 'category/'.$category->id, $data)->assertOk();
        $this->assertSame(0, $place->refresh()->status);
    }

    public function test_validation_missing_records_and_customer_permissions(): void
    {
        $category = $this->category();
        foreach (['category', 'place'] as $resource) {
            $this->api('POST', $resource)->assertUnprocessable();
            $this->api('GET', $resource.'/999')->assertNotFound();
            $this->api('DELETE', $resource.'/999')->assertNotFound();
        }
        $this->api('POST', 'category', ['en_name' => 'Invalid', 'ar_name' => 'Invalid', 'image' => UploadedFile::fake()->create('fake.png', 10, 'image/png')])->assertUnprocessable();
        $this->api('POST', 'place', ['category_id' => 999, 'en_name' => 'Invalid', 'ar_name' => 'Invalid', 'image' => UploadedFile::fake()->image('valid.png')])->assertUnprocessable();
        $customer = User::create(['user_name' => 'customer', 'type' => 0, 'status' => 1]);
        $token = $customer->createToken('test')->plainTextToken;
        $this->api('GET', 'category', [], $token)->assertForbidden();
        $this->api('POST', 'place', [], $token)->assertForbidden();
        $this->api('DELETE', 'category/'.$category->id, [], $token)->assertForbidden();
    }

    public function test_unauthenticated_api_without_accept_header_returns_json_401(): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
        $this->call('GET', '/api/v1/dashboard/category')->assertUnauthorized()->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_invalid_image_size_and_dimensions_are_rejected(): void
    {
        $data = ['en_name' => 'Too large', 'ar_name' => 'Too large'];
        $data['image'] = UploadedFile::fake()->image('big.png')->size(5121);
        $this->api('POST', 'category', $data)->assertUnprocessable();
        $data['image'] = UploadedFile::fake()->image('wide.png', 4097, 2);
        $this->api('POST', 'category', $data)->assertUnprocessable();
        $this->assertSame(0, Category::count());
    }

    public function test_names_and_slug_unique_validation_and_place_category_scope(): void
    {
        $first = $this->category();
        $second = $this->category();
        $this->api('POST', 'category', ['en_name' => $first->en_name, 'ar_name' => 'Different Arabic', 'image' => UploadedFile::fake()->image('a.png')])->assertUnprocessable();
        $this->api('POST', 'category/'.$second->id, ['en_name' => $second->en_name, 'ar_name' => $second->ar_name, 'slug' => $first->slug])->assertUnprocessable();
        $place = $this->place($first);
        $data = ['category_id' => $first->id, 'en_name' => $place->en_name, 'ar_name' => 'Different Arabic', 'image' => UploadedFile::fake()->image('a.png')];
        $this->api('POST', 'place', $data)->assertUnprocessable();
        $data['category_id'] = $second->id;
        $this->api('POST', 'place', $data)->assertCreated();
    }

    public function test_failed_category_delete_rolls_back_child_soft_deletes(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $repository = new class extends CategoryRepository
        {
            public function delete(Category $category, int $actorId): Category
            {
                throw new RuntimeException('Simulated delete failure');
            }
        };
        $service = new CategoryService($repository, $this->app->make(PlaceService::class), $this->app->make(ImageStorageService::class), $this->app->make(LoggingService::class));
        try {
            $service->delete($category, (int) $this->actor->id);
            $this->fail('Expected delete failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated delete failure', $e->getMessage());
        }
        $this->assertNull($category->refresh()->deleted_at);
        $this->assertNull($place->refresh()->deleted_at);
        $this->assertSame(1, $place->status);
    }

    public function test_category_failure_rolls_back_dependent_changes_and_keeps_old_image(): void
    {
        $category = $this->category();
        $place = $this->place($category);
        $oldPath = substr($category->image, 10);
        Storage::disk('documents')->put($oldPath, 'old');
        $repository = new class extends CategoryRepository
        {
            public function update(Category $category, array $attributes): Category
            {
                throw new RuntimeException('Simulated save failure');
            }
        };
        $service = new CategoryService($repository, $this->app->make(PlaceService::class), $this->app->make(ImageStorageService::class), $this->app->make(LoggingService::class));
        try {
            $service->update(['status' => 0], $category, (int) $this->actor->id, UploadedFile::fake()->image('new.png'));
            $this->fail('Expected save failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated save failure', $e->getMessage());
        }
        $this->assertSame(1, $place->refresh()->status);
        $this->assertSame('documents/categories/old.webp', $category->refresh()->image);
        Storage::disk('documents')->assertExists($oldPath);
        $this->assertCount(1, Storage::disk('documents')->allFiles());
    }

    public function test_failed_place_create_removes_uploaded_orphan(): void
    {
        $repository = new class extends PlaceRepository
        {
            public function create(array $attributes): Place
            {
                throw new RuntimeException('Simulated create failure');
            }
        };
        $service = new PlaceService($repository, $this->app->make(ImageStorageService::class), $this->app->make(LoggingService::class));
        try {
            $service->create(['en_name' => 'Test'], (int) $this->actor->id, UploadedFile::fake()->image('new.png'));
            $this->fail('Expected create failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated create failure', $e->getMessage());
        }
        $this->assertCount(0, Storage::disk('documents')->allFiles());
    }
}
