import { test, expect, type BrowserContext, type Page } from '@playwright/test';
import { execFileSync } from 'node:child_process';

function watch(page: Page) {
    const errors: string[] = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => {
        if (response.url().startsWith('http://localhost:8080/') && response.status() >= 400) errors.push(`${response.status()} ${response.url()}`);
    });
    return errors;
}

async function authenticateAdmin(context: BrowserContext) {
    // Tạo phiên cho tài khoản mẫu bằng CLI; không thêm đường đăng nhập thử vào production.
    const php = `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); $session=app('session')->driver(); $session->start(); $user=App\\Models\\User::where('email','admin@travelplanner.test')->firstOrFail(); Illuminate\\Support\\Facades\\Auth::login($user); $session->save(); $name=config('session.cookie'); $crypt=app('encrypter'); $prefix=Illuminate\\Cookie\\CookieValuePrefix::create($name,$crypt->getKey()); echo json_encode(['name'=>$name,'value'=>$crypt->encrypt($prefix.$session->getId(),false)]);`;
    const cookie = JSON.parse(execFileSync('docker', ['exec', '--user', 'www-data', 'travel-planner-app', 'php', '-r', php], { encoding: 'utf8' }));
    await context.addCookies([{ ...cookie, url: 'http://localhost:8080', httpOnly: true, sameSite: 'Lax' }]);
}

test('native shopping UI streams real catalog cards and stays usable on mobile', async ({ page }) => {
    const errors = watch(page);
    await page.goto('/travel-assistant');
    const send = page.getByRole('button', { name: 'Gửi', exact: true });
    await expect(send).toBeDisabled();
    await page.locator('textarea').fill('Tìm 3 điểm đến tại Đà Nẵng trong danh mục Travel Planner và hiển thị thẻ gợi ý.');
    await expect(send).toBeEnabled();
    await send.click();
    await expect(page.locator('[data-commerce-products]').first()).toBeVisible({ timeout: 150000 });
    await expect(page.locator('textarea')).not.toHaveAttribute('placeholder', 'Đang xử lý…', { timeout: 150000 });
    await page.screenshot({ path: 'test-results/commerce-shopping-desktop.png' });
    await page.setViewportSize({ width: 390, height: 844 });
    await expect(page.locator('textarea')).toBeVisible();
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    expect(overflow).toBe(false);
    await page.screenshot({ path: 'test-results/commerce-shopping-mobile.png' });
    expect(errors).toEqual([]);
});

test('native merchant dashboard loads data, properties, occupancy and assistant', async ({ page, context }) => {
    await authenticateAdmin(context);
    const errors = watch(page);
    await page.goto('/admin/commerce-agents');
    await expect(page.locator('textarea')).toBeVisible();
    await expect(page.getByText('Travel Planner', { exact: true }).first()).toBeVisible();
    await page.getByRole('button', { name: 'Properties', exact: true }).click();
    await expect(page.getByRole('searchbox', { name: /Search properties/i })).toBeVisible();
    await page.getByRole('button', { name: 'Bookings', exact: true }).first().click();
    await expect(page.locator('main')).toBeVisible();
    await page.screenshot({ path: 'test-results/commerce-merchant-desktop.png' });
    await page.setViewportSize({ width: 390, height: 844 });
    await page.screenshot({ path: 'test-results/commerce-merchant-mobile.png' });
    expect(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1)).toBe(false);
    expect(errors).toEqual([]);
});
