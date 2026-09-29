import { expect, test } from '@playwright/test';
import { accounts, adminLogin } from './support';

test('スーパー管理者が共通の単位を、店舗管理者が店舗独自の単位を追加できる', async ({ page }) => {
    page.on('dialog', (dialog) => dialog.accept());

    // スーパー管理者：共通の単位を追加する
    await adminLogin(page, accounts.superAdmin);
    await page.getByRole('link', { name: '単位管理（共通）' }).click();
    await expect(page).toHaveURL(/\/admin\/units$/);

    await page.getByRole('link', { name: '単位を追加する' }).click();
    await page.getByLabel(/単位名/).fill('パック');
    await page.getByRole('button', { name: '登録する' }).click();
    await expect(page.getByText('「パック」を登録しました。')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'パック' })).toBeVisible();

    // 管理者一覧から店舗管理者としてログインする
    await page.goto('/admin/admins');
    await page
        .getByRole('row', { name: new RegExp(accounts.shop1Admin.email) })
        .getByRole('button', { name: '店舗管理者でログイン' })
        .click();
    await expect(page).toHaveURL(/\/admin\/dashboard/);

    // 店舗管理者：共通の単位は表示のみ、自店舗の単位を追加する
    await page.getByRole('link', { name: '単位管理', exact: true }).click();
    await expect(page).toHaveURL(/\/admin\/shop1\/units$/);
    await expect(page.getByRole('listitem').filter({ hasText: 'パック' })).toBeVisible();

    await page.getByRole('link', { name: '単位を追加する' }).click();
    await page.getByLabel(/単位名/).fill('個');
    await page.getByRole('button', { name: '登録する' }).click();
    await expect(page.getByText('単位名はすでに使用されています。')).toBeVisible();

    await page.getByLabel(/単位名/).fill('束');
    await page.getByRole('button', { name: '登録する' }).click();
    await expect(page.getByText('「束」を登録しました。')).toBeVisible();
    await expect(page.getByRole('cell', { name: '束' })).toBeVisible();

    // 商品登録画面では共通の単位と自店舗の単位を選べる
    await page.goto('/admin/shop1/products/create');
    await expect(page.locator('#unit_id option')).toHaveText(['選択してください', '個', '箱', 'kg', 'パック', '束']);
});
