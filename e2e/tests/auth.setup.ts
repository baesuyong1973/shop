import { test as setup } from '@playwright/test';
import { accounts, adminLogin, adminState } from './support';

// Log each admin in once through the real 2FA flow (this is the test of the
// successful login), then save the session for the specs to reuse, since
// every admin login waits for the code mail and is the slowest step.
setup('スーパー管理者で2段階認証ログインできる', async ({ page }) => {
    await adminLogin(page, accounts.superAdmin);
    await page.context().storageState({ path: adminState.superAdmin });
});

setup('店舗管理者で2段階認証ログインできる', async ({ page }) => {
    await adminLogin(page, accounts.shop1Admin);
    await page.context().storageState({ path: adminState.shop1Admin });
});
