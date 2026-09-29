import { expect, test } from '@playwright/test';
import { accounts, addToCart } from './support';

test('お客様が商品をカートに入れ、ログインして注文できる', async ({ page }) => {
    // 店舗を選んで商品をカートに入れる（未ログインでも可能）
    await addToCart(page, 'Shop1', 'りんご', 2);

    await expect(page).toHaveURL(/\/shops\/shop1\/cart/);
    await expect(page.getByText('合計金額：¥600')).toBeVisible();

    // 未ログインでは注文ボタンの代わりにログイン案内が出る
    await expect(page.getByRole('button', { name: '注文する' })).toHaveCount(0);
    await page.getByRole('link', { name: 'ログインして注文する' }).click();

    await page.getByLabel('メールアドレス').fill(accounts.customer.email);
    await page.getByLabel('パスワード').fill(accounts.customer.password);
    await page.getByRole('button', { name: 'ログイン' }).click();
    await expect(page).toHaveURL(/\/dashboard/);

    // ログイン前にカートへ入れた商品が残っている
    await page.goto('/shops/shop1/cart');
    await expect(page.getByText('合計金額：¥600')).toBeVisible();

    page.once('dialog', (dialog) => {
        expect(dialog.message()).toBe('この内容で注文しますか？');
        dialog.accept();
    });
    await page.getByRole('button', { name: '注文する' }).click();

    await expect(page).toHaveURL(/\/shops\/shop1$/);
    await expect(page.getByText(/ご注文ありがとうございます。（注文番号：\d+）/)).toBeVisible();

    // カートは空になり、在庫が減っている
    await page.goto('/shops/shop1/cart');
    await expect(page.getByText('カートに商品がありません。')).toBeVisible();

    await page.goto('/shops/shop1');
    await page.getByRole('link', { name: /りんご/ }).click();
    await expect(page.getByText('在庫：48点')).toBeVisible();

    // 注文履歴に表示される
    await page.goto('/dashboard');
    await expect(page.getByText('Shop1').first()).toBeVisible();
});

test('カートから商品を削除できる', async ({ page }) => {
    await addToCart(page, 'Shop1', 'トマト', 1);

    page.once('dialog', (dialog) => dialog.accept());
    await page.getByRole('button', { name: '削除' }).locator('visible=true').click();

    await expect(page.getByText('カートに商品がありません。')).toBeVisible();
});

test('店舗ページで商品名を検索できる', async ({ page }) => {
    await page.goto('/shops/shop1');
    await expect(page.getByRole('link', { name: /トマト/ })).toBeVisible();

    await page.getByLabel('商品名').fill('りんご');
    await page.getByRole('button', { name: '検索' }).click();

    await expect(page.getByRole('link', { name: /りんご/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /トマト/ })).toHaveCount(0);
});
