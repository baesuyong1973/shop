<?php

namespace Tests\Feature\Admin;

use App\Mail\AdminTwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    /**
     * Log in with the password and return the 2FA code that was mailed.
     */
    private function loginAndCaptureCode(string $email = 'super@example.com'): string
    {
        Mail::fake();

        $this->post(route('admin.login.attempt'), [
            'email' => $email,
            'password' => 'password',
        ])->assertRedirect(route('admin.verify.show'));

        $code = null;
        Mail::assertSent(AdminTwoFactorCode::class, function (AdminTwoFactorCode $mail) use ($email, &$code) {
            $code = $mail->code;

            return $mail->hasTo($email);
        });

        return $code;
    }

    public function test_管理者ログイン画面が表示される(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Login'));
    }

    public function test_正しいパスワードで確認コードがメール送信される(): void
    {
        $admin = $this->makeSuperAdmin();

        $code = $this->loginAndCaptureCode();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertNotNull($admin->fresh()->two_factor_code);
        $this->assertNotSame($code, $admin->fresh()->two_factor_code);
        $this->assertGuest('admin');
    }

    public function test_誤ったパスワードではログインできずメールも送られない(): void
    {
        Mail::fake();
        $this->makeSuperAdmin();

        $this->post(route('admin.login.attempt'), [
            'email' => 'super@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
        $this->assertGuest('admin');
    }

    public function test_存在しないメールアドレスではログインできない(): void
    {
        Mail::fake();

        $this->post(route('admin.login.attempt'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_正しい確認コードでログインできる(): void
    {
        $admin = $this->makeSuperAdmin();
        $code = $this->loginAndCaptureCode();

        $this->get(route('admin.verify.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Verify'));

        $this->post(route('admin.verify'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNull($admin->fresh()->two_factor_code);
        $this->assertNull($admin->fresh()->two_factor_expires_at);
    }

    public function test_誤った確認コードではログインできない(): void
    {
        $this->makeSuperAdmin();
        $code = $this->loginAndCaptureCode();
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->post(route('admin.verify'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');

        $this->assertGuest('admin');
    }

    public function test_有効期限切れの確認コードではログインできない(): void
    {
        $this->makeSuperAdmin();
        $code = $this->loginAndCaptureCode();

        $this->travel(11)->minutes();

        $this->post(route('admin.verify'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertGuest('admin');
    }

    public function test_使用済みの確認コードは再利用できない(): void
    {
        $admin = $this->makeSuperAdmin();
        $code = $this->loginAndCaptureCode();

        $this->post(route('admin.verify'), ['code' => $code]);
        $this->post(route('admin.logout'));

        $this->withSession(['admin_2fa_id' => $admin->id])
            ->post(route('admin.verify'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertGuest('admin');
    }

    public function test_確認コードが未入力の場合はエラーになる(): void
    {
        $this->makeSuperAdmin();
        $this->loginAndCaptureCode();

        $this->post(route('admin.verify'), [])->assertSessionHasErrors('code');
    }

    public function test_パスワード認証前は確認コード画面に進めない(): void
    {
        $this->get(route('admin.verify.show'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.verify'), ['code' => '123456'])->assertRedirect(route('admin.login'));
        $this->post(route('admin.verify.resend'))->assertRedirect(route('admin.login'));
    }

    public function test_削除された管理者のコード再送はログイン画面に戻される(): void
    {
        $this->withSession(['admin_2fa_id' => 999999])
            ->post(route('admin.verify.resend'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_確認コードを再送すると新しいコードが届き古いコードは無効になる(): void
    {
        $this->makeSuperAdmin();
        $oldCode = $this->loginAndCaptureCode();

        Mail::fake();
        $this->from(route('admin.verify.show'))
            ->post(route('admin.verify.resend'))
            ->assertRedirect(route('admin.verify.show'))
            ->assertSessionHas('status');

        $newCode = null;
        Mail::assertSent(AdminTwoFactorCode::class, function (AdminTwoFactorCode $mail) use (&$newCode) {
            $newCode = $mail->code;

            return true;
        });

        if ($oldCode !== $newCode) {
            $this->post(route('admin.verify'), ['code' => $oldCode])->assertSessionHasErrors('code');
        }

        $this->post(route('admin.verify'), ['code' => $newCode])->assertRedirect(route('admin.dashboard'));
    }

    public function test_ログアウトできる(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_未ログインでは管理画面に入れない(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みでログイン画面を開くとダッシュボードに移動する(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_ダッシュボードが表示される(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard'));
    }
}
