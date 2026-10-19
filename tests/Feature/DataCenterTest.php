<?php

namespace Tests\Feature;

use App\Models\DataCenterAccount;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataCenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'data_center_testing');
        config()->set('database.connections.data_center_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('data_center_testing');
        DB::setDefaultConnection('data_center_testing');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('isAdmin')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_center_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('provider', 100)->nullable();
            $table->text('account_name');
            $table->text('username')->nullable();
            $table->text('email')->nullable();
            $table->text('password')->nullable();
            $table->text('recovery_email')->nullable();
            $table->text('phone')->nullable();
            $table->text('url')->nullable();
            $table->longText('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_non_admin_cannot_access_data_center(): void
    {
        $user = User::factory()->create(['isAdmin' => false]);

        $this->actingAs($user)
            ->get(route('data-center.index'))
            ->assertForbidden();
    }

    public function test_admin_can_store_an_encrypted_account(): void
    {
        $admin = User::factory()->create(['isAdmin' => true]);

        $this->actingAs($admin)
            ->post(route('data-center.store'), [
                'category' => 'Instagram',
                'provider' => 'Meta',
                'account_name' => 'Brand Utama',
                'username' => 'brand.utama',
                'email' => 'brand@example.com',
                'password' => 'rahasia-kuat',
                'url' => 'https://instagram.com/',
            ])
            ->assertRedirect();

        $account = DataCenterAccount::firstOrFail();
        $raw = DB::table('data_center_accounts')->where('id', $account->id)->first();

        $this->assertSame('Brand Utama', $account->account_name);
        $this->assertSame('rahasia-kuat', $account->password);
        $this->assertNotSame('Brand Utama', $raw->account_name);
        $this->assertNotSame('rahasia-kuat', $raw->password);
    }

    public function test_password_reveal_requires_current_admin_password(): void
    {
        $admin = User::factory()->create([
            'isAdmin' => true,
            'password' => 'password-admin',
        ]);
        $account = DataCenterAccount::create([
            'category' => 'ERP',
            'account_name' => 'ERP Utama',
            'password' => 'password-tersimpan',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('data-center.reveal', $account), ['current_password' => 'salah'])
            ->assertUnprocessable();

        $this->actingAs($admin)
            ->postJson(route('data-center.reveal', $account), ['current_password' => 'password-admin'])
            ->assertOk()
            ->assertJson(['password' => 'password-tersimpan'])
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
