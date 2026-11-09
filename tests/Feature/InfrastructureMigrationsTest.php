<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class InfrastructureMigrationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'cache.stores.database.connection' => 'sqlite', 'cache.stores.database.lock_connection' => 'sqlite', 'cache.stores.database.table' => 'cache', 'cache.stores.database.lock_table' => 'cache_locks']);
        DB::purge('sqlite');
        // Guard the target before calling any migration method.
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_cache_migration_supports_database_cache_locks_and_rollback(): void
    {
        $migration = require database_path('migrations/0001_01_01_000001_create_cache_table.php');
        $migration->up();
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('cache_locks'));
        $store = Cache::store('database');
        $store->put('migration-test', 'value', 60);
        $this->assertSame('value', $store->get('migration-test'));
        $lock = $store->lock('migration-lock', 10);
        $this->assertTrue($lock->get());
        $this->assertFalse($store->lock('migration-lock', 10)->get());
        $this->assertTrue($lock->release());
        $migration->down();
        $this->assertFalse(Schema::hasTable('cache'));
        $this->assertFalse(Schema::hasTable('cache_locks'));
    }

    public function test_access_token_migration_supports_sanctum_and_rollback(): void
    {
        $migration = require database_path('migrations/2026_11_08_135800_create_personal_access_tokens_table.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumns('personal_access_tokens', ['tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'expires_at', 'last_used_at', 'created_at', 'updated_at']));
        $user = new User;
        $user->id = 42;
        $issued = $user->createToken('migration-test', ['*'], now()->addHours(10));
        $token = PersonalAccessToken::findToken($issued->plainTextToken);
        $this->assertNotNull($token);
        $this->assertEquals(42, $token->tokenable_id);
        $this->assertNotSame($issued->plainTextToken, $token->token);
        $this->assertEqualsWithDelta(now()->addHours(10)->timestamp, $token->expires_at->timestamp, 2);
        $migration->down();
        $this->assertFalse(Schema::hasTable('personal_access_tokens'));
    }
}
