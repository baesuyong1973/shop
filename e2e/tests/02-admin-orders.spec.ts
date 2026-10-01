import { expect, test } from '@playwright/test';
import { accounts, addToCart, adminState, clearMailTo, customerLogin, waitForTwoFactorCode } from './support';

test('誤った確認コードでは管理画面にログインできない', async ({ page }) => {
    await clearMailTo(accounts.superAdmin.email);

    await page.goto('/admin/login');
    await page.getByLabel('メールアドレス').fill(accounts.superAdmin.email);
    await page.getByLabel('パスワード').fill(accounts.superAdmin.password);
    await page.getByRole('button', { name: 'ログイン' }).click();

    const code = await waitForTwoFactorCode(accounts.superAdmin.email);
    await page.getByLabel('確認コード').fill(code === '000000' ? '111111' : '000000');
    await page.getByRole('button', { name: '確認する' }).click();

    await expect(page.getByText('確認コードが正しくないか、有効期限が切れています。')).toBeVisible();
    await expect(page).toHaveURL(/\/admin\/verify/);
});

test('店舗管理者が注文をキャンセルすると在庫が戻る', async ({ browser }) => {
    // お客様がにんじんを3個注文する（在庫 100 → 97）
    const customer = await browser.newPage();
    await customerLogin(customer);
    await addToCart(customer, 'Shop1', 'にんじん', 3);
    customer.once('dialog', (dialog) => dialog.accept());
    await customer.getByRole('button', { name: '注文する' }).click();
    await customer.getByRole('button', { name: '支払う' }).click();
    const confirmation = customer.getByText(/ご注文ありがとうございます。（注文番号：\d+）/);
    await expect(confirmation).toBeVisible();
    const orderId = (await confirmation.textContent())!.match(/注文番号：(\d+)/)![1];

    await customer.goto('/shops/shop1');
    await customer.getByRole('link', { name: /にんじん/ }).click();
    await expect(customer.getByText('在庫：97点')).toBeVisible();

    // 店舗管理者（ログイン済みの状態から）が注文をキャンセルする
    const admin = await (await browser.newContext({ storageState: adminState.shop1Admin })).newPage();
    await admin.goto('/admin/shop1/orders');
    await expect(admin.getByText(`注文番号${orderId}`)).toBeVisible();
    await admin.goto(`/admin/shop1/orders/${orderId}`);
    await expect(admin.getByRole('cell', { name: 'にんじん' })).toBeVisible();

    admin.once('dialog', (dialog) => {
        expect(dialog.message()).toBe('この注文を「キャンセル」にしますか？');
        dialog.accept();
    });
    await admin.getByRole('button', { name: 'キャンセル' }).click();
    // 前払い（にんじん3個 ¥450 の1% = ¥4.5 を切り上げて ¥5）が返金される
    await expect(admin.getByText('注文のステータスを更新し、前払い金 ¥5 を返金しました。')).toBeVisible();

    // 在庫が注文前の数に戻る
    await customer.reload();
    await expect(customer.getByText('在庫：100点')).toBeVisible();
});
