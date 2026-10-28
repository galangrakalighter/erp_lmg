<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginOtpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'login_otp_testing');
        config()->set('database.connections.login_otp_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        DB::purge('login_otp_testing');
        DB::setDefaultConnection('login_otp_testing');
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
        Mail::fake();
    }

    public function test_valid_credentials_send_otp_without_logging_user_in(): void
    {
        $user = User::factory()->create(['password' => 'password-benar']);

        $this->post(route('post-login'), ['email' => $user->email, 'password' => 'password-benar'])
            ->assertRedirect(route('login.otp.form'))
            ->assertSessionHas('login_otp.user_id', $user->id);

        $this->assertGuest();
        Mail::assertSent(LoginOtpMail::class, fn (LoginOtpMail $mail) => strlen($mail->code) === 6);
    }

    public function test_admin_email_logs_in_without_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@gmail.com',
            'password' => 'password-benar',
        ]);

        $this->post(route('post-login'), [
            'email' => 'admin@gmail.com',
            'password' => 'password-benar',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('login_otp'));
        Mail::assertNothingSent();
    }

    public function test_correct_otp_completes_login(): void
    {
        $user = User::factory()->create(['password' => 'password-benar']);
        $this->post(route('post-login'), ['email' => $user->email, 'password' => 'password-benar']);

        $code = null;
        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $this->post(route('login.otp.verify'), ['otp' => $code])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('login_otp'));
    }

    public function test_wrong_otp_does_not_log_user_in(): void
    {
        $user = User::factory()->create(['password' => 'password-benar']);
        $this->post(route('post-login'), ['email' => $user->email, 'password' => 'password-benar']);

        $this->post(route('login.otp.verify'), ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertSame(1, session('login_otp.attempts'));
    }

    public function test_locked_account_does_not_receive_otp(): void
    {
        $user = User::factory()->create(['password' => 'password-benar', 'is_locked' => true]);

        $this->post(route('post-login'), ['email' => $user->email, 'password' => 'password-benar'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
        $this->assertGuest();
    }
}
