import { randomUUID } from 'node:crypto';
import { expect, test, type Page } from '@playwright/test';

async function register(page: Page) {
    const email = randomUUID() + '@example.test';
    const password = 'DailyFlow-test-2026!';
    await page.goto('/register');
    await page.locator('#name').fill('DailyFlow Browser Test');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill(password);
    await page.locator('#passwordConfirmation').fill(password);
    await page.locator('button[type=submit]').click();
    await expect(page).toHaveURL(/\/today$/);
    return { email, password };
}

async function addTask(page: Page, title: string, tag?: string) {
    const input = page.getByPlaceholder(/افزودن تسک جدید/);
    await input.fill(title);
    if (tag) await page.getByRole('textbox', { name: 'برچسب‌های تسک' }).fill(tag);
    await input.press('Enter');
    await expect(page.getByRole('button', { name: title })).toBeVisible();
}

test('registration, protected views and login persist across reload', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/login/);
    const credentials = await register(page);
    await page.reload();
    await expect(page).toHaveURL(/\/today$/);
    await page.getByRole('button', { name: /خروج/ }).first().click();
    await expect(page).toHaveURL(/\/login/);
    await page.locator('#email').fill(credentials.email);
    await page.locator('#password').fill(credentials.password);
    await page.locator('button[type=submit]').click();
    await expect(page).toHaveURL(/\/today$/);
});

test('task completion updates the dashboard and supports reopening', async ({ page }) => {
    await register(page);
    await addTask(page, 'Browser task', 'browser-tag');
    await expect(page.getByText('browser-tag', { exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Browser task' }).click();
    const details = page.getByRole('dialog', { name: 'جزئیات تسک' });
    const title = details.getByRole('textbox', { name: 'عنوان تسک' });
    await title.fill('Browser lifecycle task');
    await title.press('Enter');
    const subtask = details.getByPlaceholder(/افزودن مورد جدید/);
    await subtask.fill('Prepare notes');
    await subtask.press('Enter');
    await expect(details.getByText('Prepare notes', { exact: true })).toHaveCount(1);
    await page.keyboard.press('Escape');
    await Promise.all([
        page.waitForResponse(response => response.url().endsWith('/complete') && response.ok()),
        page.getByRole('checkbox', { name: 'Browser lifecycle task' }).click(),
    ]);
    await expect(page.getByRole('button', { name: 'Browser lifecycle task' })).toBeHidden();
    await page.goto('/dashboard');
    await expect(page.getByText('100٪')).toBeVisible();
    await page.goto('/completed');
    await page.getByRole('checkbox', { name: 'Browser lifecycle task' }).click();
    await expect(page.getByRole('button', { name: 'Browser lifecycle task' })).toBeHidden();
    await page.goto('/today');
    await page.getByRole('button', { name: 'Browser lifecycle task' }).click();
    page.once('dialog', confirm => confirm.accept());
    await page.getByRole('button', { name: 'حذف تسک', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Browser lifecycle task' })).toBeHidden();
});

test('keyboard command palette searches tasks and opens details', async ({ page }) => {
    await register(page);
    await addTask(page, 'Keyboard search');
    await page.keyboard.press('ControlOrMeta+k');
    const search = page.getByRole('combobox', { name: 'جست‌وجوی تسک و پروژه' });
    await expect(search).toBeFocused();
    await search.fill('Keyboard search');
    await expect(page.getByRole('option', { name: /Keyboard search/ })).toBeVisible();
    await search.press('Enter');
    await expect(page.getByRole('dialog', { name: 'جزئیات تسک' })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.getByRole('dialog', { name: 'جزئیات تسک' })).toBeHidden();
});

test('creating a favorite project makes it available in the sidebar', async ({ page }) => {
    await register(page);
    await page.goto('/projects');
    await page.getByRole('button', { name: /پروژه جدید/ }).first().click();
    const dialog = page.getByRole('dialog');
    await dialog.getByPlaceholder(/مثلاً: طراحی/).fill('Browser project');
    await dialog.getByRole('button', { name: 'بنفش', exact: true }).click();
    await dialog.getByRole('button', { name: 'ستاره', exact: true }).click();
    await dialog.getByRole('checkbox', { name: /مورد علاقه/ }).click();
    await dialog.locator('button[type=submit]').click();
    const project = page.locator('aside').getByText('Browser project', { exact: true }).first();
    await expect(project).toBeVisible();
    await project.click();
    await addTask(page, 'Project progress task');
    await page.getByRole('checkbox', { name: 'Project progress task' }).click();
    await expect(page.getByText('1 از 1 تسک (100٪)')).toBeVisible();
});
