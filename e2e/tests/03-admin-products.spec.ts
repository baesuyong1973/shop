import { expect, test } from '@playwright/test';
import { adminState } from './support';

// 1x1 pixel PNG, enough for the server-side image processing.
const PNG = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'base64',
);

test.use({ storageState: adminState.shop1Admin });

test('店舗管理者が画像付きで商品を登録すると店舗ページに表示される', async ({ page }) => {
    const productName = `E2E商品-${Date.now()}`;

    await page.goto('/admin/shop1/products/create');

    await page.getByLabel('商品名').fill(productName);
    await page.getByLabel('商品画像').setInputFiles({ name: 'product.png', mimeType: 'image/png', buffer: PNG });
    await page.getByLabel('価格（円）').fill('980');
    await page.getByLabel('在庫数').fill('12');
    await page.getByRole('button', { name: '登録する' }).click();

    await expect(page).toHaveURL(/\/admin\/shop1\/products$/);
    await expect(page.getByText('商品を登録しました。')).toBeVisible();
    await expect(page.getByText(productName).locator('visible=true')).toBeVisible();

    // お客様側の店舗ページに表示され、画像も読み込める
    await page.goto('/shops/shop1');
    const card = page.getByRole('link', { name: new RegExp(productName) });
    await expect(card).toBeVisible();

    const image = card.locator('img');
    await expect(image).toBeVisible();
    await expect.poll(() => image.evaluate((img: HTMLImageElement) => img.naturalWidth)).toBeGreaterThan(0);
});
