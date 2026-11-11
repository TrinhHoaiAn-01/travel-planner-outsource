import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

test.describe('Admin User Modal DOM Safety', () => {
    let testUserId: number;

    test.beforeAll(async () => {
        // Create synthetic user with XSS probe payload in name and avatar
        const script = `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); App\\Models\\User::withTrashed()->where('email','xss.probe@example.test')->forceDelete(); $u=App\\Models\\User::create(['name'=>'Nguyễn Văn An" onmouseover="window.__xss_probe=true" onerror="window.__xss_probe=true"','email'=>'xss.probe@example.test','password'=>bcrypt('password'),'role'=>'user','user_code'=>'USR-XSS-999','city'=>'Đà Nẵng','phone'=>'0901234567','avatar'=>'https://example.com/avatar.jpg" onerror="window.__xss_probe=true"','is_active'=>true]); echo $u->id;`;
        const output = execFileSync('php', ['-r', script], { encoding: 'utf8' });
        testUserId = parseInt(output.trim(), 10);
    });

    test.afterAll(async () => {
        if (testUserId) {
            const cleanup = `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); App\\Models\\User::where('email','xss.probe@example.test')->forceDelete();`;
            execFileSync('php', ['-r', cleanup], { encoding: 'utf8' });
        }
    });

    test('modal does not execute injected event handler and preserves Vietnamese details', async ({ page, context, baseURL }) => {
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        page.on('response', async res => {
            if (res.url().includes('/admin/users/')) {
                console.log('USER RESPONSE:', res.status(), await res.text());
            }
        });

        // Authenticate as admin via session cookie
        const phpAuth = `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); $session=app('session')->driver(); $session->start(); $user=App\\Models\\User::where('email','admin@travelplanner.test')->firstOrFail(); Illuminate\\Support\\Facades\\Auth::login($user); $session->save(); $name=config('session.cookie'); $crypt=app('encrypter'); $prefix=Illuminate\\Cookie\\CookieValuePrefix::create($name,$crypt->getKey()); echo json_encode(['name'=>$name,'value'=>$crypt->encrypt($prefix.$session->getId(),false)]);`;
        const cookieJson = JSON.parse(execFileSync('php', ['-r', phpAuth], { encoding: 'utf8' }).trim());
        await context.addCookies([{ ...cookieJson, url: baseURL || 'http://localhost:8000', httpOnly: true, sameSite: 'Lax' }]);

        // Navigate to admin users
        await page.goto(`${baseURL}/admin/users`);
        await expect(page.locator('#viewUserModal')).toBeAttached();

        // Trigger openViewUserModal for the test user
        await page.evaluate((id) => {
            (window as any).__xss_probe = undefined;
            (window as any).openViewUserModal(id);
        }, testUserId);

        const modal = page.locator('#viewUserModal');
        await expect(modal).toHaveClass(/is-active/);

        // Wait a moment for network fetch and DOM rendering
        await page.waitForTimeout(1000);

        // Verify window.__xss_probe was NOT executed
        const probeTriggered = await page.evaluate(() => (window as any).__xss_probe);
        expect(probeTriggered).toBeUndefined();

        // Verify Vietnamese text and details are rendered safely
        const modalBody = page.locator('#viewUserModalBody');
        await expect(modalBody).toContainText('Nguyễn Văn An');
        await expect(modalBody).toContainText('Đà Nẵng');
        await expect(modalBody).toContainText('Địa chỉ Email');
        await expect(modalBody).toContainText('xss.probe@example.test');
        await expect(modalBody).toContainText('Hoạt động (Active)');
    });
});
