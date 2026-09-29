<?php

namespace Tests\Feature;

use App\Mail\AdminOrderNotification;
use App\Mail\AdminTwoFactorCode;
use App\Mail\ContactInquiry;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者の確認コードメールにコードが記載される(): void
    {
        $mail = new AdminTwoFactorCode('123456');

        $mail->assertHasSubject('管理画面ログイン用の確認コード');
        $mail->assertSeeInHtml('123456');
        $this->assertSame([], $mail->attachments());
    }

    public function test_お問い合わせメールに入力内容と種別名が記載される(): void
    {
        $mail = new ContactInquiry(
            name: '山田太郎',
            email: 'taro@example.com',
            phone: '090-1234-5678',
            type: 'shop_registration',
            message: '出店したいです。',
        );

        $mail->assertHasSubject('お問合せがありました');
        $mail->assertSeeInHtml('山田太郎');
        $mail->assertSeeInHtml('taro@example.com');
        $mail->assertSeeInHtml('090-1234-5678');
        $mail->assertSeeInHtml('出店したいです。');
        $mail->assertDontSeeInHtml('shop_registration');
    }

    public function test_店舗への新規注文通知メールに注文内容が記載される(): void
    {
        $shop = Shop::create(['name' => 'テスト店舗', 'slug' => 'test-shop', 'is_active' => true]);
        $user = User::factory()->create(['name' => '注文者']);
        $order = Order::create([
            'shop_id' => $shop->id,
            'user_id' => $user->id,
            'total_amount' => 3000,
            'status' => 'placed',
        ]);
        $order->items()->create([
            'product_name' => 'りんご',
            'unit_price' => 1500,
            'quantity' => 2,
            'subtotal' => 3000,
        ]);

        $mail = new AdminOrderNotification($order->load('items', 'shop', 'user'));

        $mail->assertHasSubject("新規注文が入りました（注文番号：{$order->id}）");
        $mail->assertSeeInHtml('注文者');
        $mail->assertSeeInHtml('テスト店舗');
        $mail->assertSeeInHtml('りんご');
        $mail->assertSeeInHtml('¥3,000');
    }
}
