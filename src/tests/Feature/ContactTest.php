<?php

namespace Tests\Feature;

use App\Mail\ContactInquiry;
use App\Models\Admin;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(string $role, string $email, ?int $shopId = null): Admin
    {
        $admin = new Admin([
            'name' => 'Admin',
            'email' => $email,
            'password' => 'password',
        ]);
        $admin->role = $role;
        $admin->shop_id = $shopId;
        $admin->save();

        return $admin;
    }

    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'phone' => '090-1234-5678',
            'type' => 'bug',
            'message' => '画像が表示されません。',
        ], $overrides);
    }

    public function test_お問い合わせフォームが表示される(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contact/Create')
                ->where('completed', false));
    }

    public function test_お問い合わせはスーパー管理者だけに送信される(): void
    {
        Mail::fake();

        $this->makeAdmin(Admin::ROLE_SUPER_ADMIN, 'super1@example.com');
        $this->makeAdmin(Admin::ROLE_SUPER_ADMIN, 'super2@example.com');
        $shop = Shop::create(['name' => 'Shop', 'slug' => 'shop', 'is_active' => true]);
        $this->makeAdmin(Admin::ROLE_SHOP_ADMIN, 'shop-admin@example.com', $shop->id);

        $this->post(route('contact.store'), $this->validInput())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('completed', true);

        Mail::assertSent(ContactInquiry::class, function (ContactInquiry $mail) {
            return $mail->hasTo('super1@example.com')
                && $mail->hasTo('super2@example.com')
                && ! $mail->hasTo('shop-admin@example.com')
                && $mail->name === '山田太郎'
                && $mail->type === 'bug'
                && $mail->message === '画像が表示されません。';
        });
        Mail::assertSentCount(1);
    }

    public function test_送信後に完了メッセージが表示される(): void
    {
        $this->withSession(['completed' => true])
            ->get(route('contact.create'))
            ->assertInertia(fn (Assert $page) => $page->where('completed', true));
    }

    public function test_スーパー管理者がいない場合はメールを送らずに完了する(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), $this->validInput())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('completed', true);

        Mail::assertNothingSent();
    }

    public function test_必須項目が未入力の場合はエラーになる(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'type', 'message']);

        Mail::assertNothingSent();
    }

    public function test_不正な値は拒否される(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), $this->validInput([
            'email' => 'not-an-email',
            'type' => 'unknown',
            'phone' => str_repeat('1', 21),
            'message' => str_repeat('あ', 2001),
        ]))->assertSessionHasErrors(['email', 'type', 'phone', 'message']);

        Mail::assertNothingSent();
    }

    public function test_すべてのお問い合わせ種別を受け付ける(): void
    {
        Mail::fake();

        foreach (['bug', 'shop_registration', 'other'] as $type) {
            $this->post(route('contact.store'), $this->validInput(['type' => $type]))
                ->assertSessionHasNoErrors();
        }
    }

    public function test_連続送信は回数制限される(): void
    {
        Mail::fake();

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('contact.store'), $this->validInput())->assertRedirect();
        }

        $this->post(route('contact.store'), $this->validInput())->assertTooManyRequests();
    }
}
