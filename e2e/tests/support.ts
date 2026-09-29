import { expect, type Page } from '@playwright/test';

const MAILPIT_URL = 'http://localhost:8025';

/** Accounts created by the default DatabaseSeeder. */
export const accounts = {
    customer: { email: 'test@example.com', password: 'password' },
    superAdmin: { email: 'admin@example.com', password: 'password' },
    shop1Admin: { email: 'shop1@example.com', password: 'password' },
};

/**
 * Mailpit is shared with the dev environment, so clear only the messages
 * addressed to this recipient before triggering a new one.
 */
export async function clearMailTo(email: string): Promise<void> {
    await fetch(`${MAILPIT_URL}/api/v1/search?query=${encodeURIComponent(`to:${email}`)}`, {
        method: 'DELETE',
    });
}

/**
 * Wait for the admin 2FA mail sent to this address and return its code.
 */
export async function waitForTwoFactorCode(email: string): Promise<string> {
    let code: string | undefined;

    await expect
        .poll(
            async () => {
                const search = await fetch(
                    `${MAILPIT_URL}/api/v1/search?query=${encodeURIComponent(`to:${email}`)}`,
                ).then((r) => r.json());
                const latest = search.messages?.[0];
                if (!latest) {
                    return undefined;
                }
                const message = await fetch(`${MAILPIT_URL}/api/v1/message/${latest.ID}`).then((r) => r.json());
                code = message.Text.match(/\b(\d{6})\b/)?.[1];
                return code;
            },
            { message: `2FA mail to ${email}`, timeout: 15_000 },
        )
        .toBeTruthy();

    return code!;
}

export async function customerLogin(page: Page, account = accounts.customer): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('メールアドレス').fill(account.email);
    await page.getByLabel('パスワード').fill(account.password);
    await page.getByRole('button', { name: 'ログイン' }).click();
    await expect(page).toHaveURL(/\/dashboard/);
}

/**
 * Log into the admin panel through the real password + emailed-code flow.
 */
export async function adminLogin(page: Page, account = accounts.superAdmin): Promise<void> {
    await clearMailTo(account.email);

    await page.goto('/admin/login');
    await page.getByLabel('メールアドレス').fill(account.email);
    await page.getByLabel('パスワード').fill(account.password);
    await page.getByRole('button', { name: 'ログイン' }).click();
    await expect(page).toHaveURL(/\/admin\/verify/);

    const code = await waitForTwoFactorCode(account.email);
    await page.getByLabel('確認コード').fill(code);
    await page.getByRole('button', { name: '確認する' }).click();
    await expect(page).toHaveURL(/\/admin\/dashboard/);
}

/**
 * Add a product to the cart from its product page.
 */
export async function addToCart(page: Page, shopName: string, productName: string, quantity: number): Promise<void> {
    await page.goto('/');
    await page.getByRole('link', { name: shopName }).click();
    await page.getByRole('link', { name: new RegExp(productName) }).click();
    await page.getByLabel('注文個数').fill(String(quantity));
    await page.getByRole('button', { name: 'カートに追加する' }).click();
    await expect(page.getByText('カートに追加しました。')).toBeVisible();
}
