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
    await Promise.all([
        page.waitForResponse(response => response.url().endsWith('/auth/login') && response.ok()),
        page.locator('button[type=submit]').click(),
    ]);
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

test('kanban drag and mobile status selection persist across reloads', async ({ page }) => {
    await register(page);
    await page.goto('/projects');
    await page.getByRole('button', { name: /پروژه جدید/ }).first().click();
    const dialog = page.getByRole('dialog');
    await dialog.getByPlaceholder(/مثلاً: طراحی/).fill('Board project');
    await dialog.locator('button[type=submit]').click();
    await page.locator('aside').getByText('Board project', { exact: true }).first().click();
    await addTask(page, 'Board task');
    const boardMode = page.getByRole('button', { name: 'کانبان', exact: true });
    await boardMode.click();
    await expect(boardMode).toHaveAttribute('aria-pressed', 'true');

    const pending = page.getByRole('region', { name: 'در انتظار', exact: true });
    const progress = page.getByRole('region', { name: 'در حال انجام', exact: true });
    const completed = page.getByRole('region', { name: 'انجام‌شده', exact: true });
    const handle = pending.getByRole('listitem').filter({ hasText: 'Board task' }).getByTitle('کشیدن تسک');
    await handle.hover();
    await page.mouse.down();
    const dropZone = await progress.boundingBox();
    expect(dropZone).not.toBeNull();
    const x = dropZone!.x + dropZone!.width / 2;
    const y = dropZone!.y + 32;
    await page.mouse.move(x, y, { steps: 12 });
    await page.mouse.move(x + 2, y + 2);
    await page.mouse.up();
    await expect(progress.getByRole('button', { name: 'Board task' })).toBeVisible();

    await page.setViewportSize({ width: 390, height: 844 });
    await page.reload();
    await expect(boardMode).toHaveAttribute('aria-pressed', 'true');
    await progress.getByRole('combobox', { name: 'وضعیت Board task' }).selectOption('completed');
    await expect(completed.getByRole('button', { name: 'Board task' })).toBeVisible();

    await page.getByRole('button', { name: 'منو', exact: true }).click();
    const menu = page.getByRole('dialog', { name: 'منوی برنامه' });
    await expect(menu.getByRole('button', { name: 'بستن منو' })).toBeFocused();
    await menu.getByRole('button', { name: 'افزودن پروژه جدید' }).click();
    await expect(menu).toBeHidden();
    const mobileProject = page.getByRole('dialog', { name: 'پروژه جدید' });
    await expect(mobileProject).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(mobileProject).toBeHidden();
    await page.getByRole('button', { name: 'منو', exact: true }).click();
    await menu.getByRole('link', { name: 'داشبورد' }).click();
    await expect(page.getByText('100٪')).toBeVisible();
    await page.getByRole('button', { name: 'تغییر تم' }).click();
    await expect(page.getByRole('button', { name: '۳۰ روز' })).toBeVisible();
    expect(await page.locator('html').evaluate(el => el.scrollWidth <= window.innerWidth)).toBe(true);
});

test('global kanban includes project and inbox tasks and saves status changes', async ({ page }) => {
    await register(page);
    await addTask(page, 'Unassigned board task');
    await page.goto('/projects');
    await page.getByRole('button', { name: /پروژه جدید/ }).first().click();
    const dialog = page.getByRole('dialog');
    await dialog.getByPlaceholder(/مثلاً: طراحی/).fill('Global board project');
    await dialog.locator('button[type=submit]').click();
    await page.locator('aside').getByText('Global board project', { exact: true }).first().click();
    await addTask(page, 'Assigned board task');

    await page.getByRole('navigation', { name: 'ناوبری اصلی' }).getByRole('link', { name: 'کانبان همهٔ تسک‌ها' }).click();
    const pending = page.getByRole('region', { name: 'در انتظار', exact: true });
    const assigned = pending.getByRole('listitem').filter({ hasText: 'Assigned board task' });
    await expect(assigned.getByText('Global board project', { exact: true })).toBeVisible();
    await expect(pending.getByRole('listitem').filter({ hasText: 'Unassigned board task' }).getByText('بدون پروژه')).toBeVisible();

    await assigned.getByRole('combobox', { name: 'وضعیت Assigned board task' }).selectOption('in_progress');
    const progress = page.getByRole('region', { name: 'در حال انجام', exact: true });
    await expect(progress.getByRole('button', { name: 'Assigned board task' })).toBeVisible();
    await page.reload();
    await expect(progress.getByRole('button', { name: 'Assigned board task' })).toBeVisible();
});

test('task details open centrally and save the in-progress status', async ({ page }) => {
    await register(page);
    await addTask(page, 'Task details status');
    await page.getByRole('button', { name: 'Task details status', exact: true }).click();
    const details = page.getByRole('dialog', { name: 'جزئیات تسک' });
    const bounds = await details.boundingBox();
    const viewport = page.viewportSize();
    expect(bounds).not.toBeNull();
    expect(Math.abs(bounds!.x + bounds!.width / 2 - viewport!.width / 2)).toBeLessThan(8);
    expect(Math.abs(bounds!.y + bounds!.height / 2 - viewport!.height / 2)).toBeLessThan(8);
    const status = details.getByRole('combobox', { name: 'وضعیت تسک' });
    await status.focus();
    await Promise.all([
        page.waitForResponse(response => response.request().method() === 'PUT' && response.url().includes('/tasks/') && response.ok()),
        status.selectOption('in_progress'),
    ]);
    await expect(status).toHaveValue('in_progress');
    await expect(status).toBeFocused();
    await page.keyboard.press('Escape');
    await page.goto('/kanban');
    await expect(page.getByRole('region', { name: 'در حال انجام', exact: true })
        .getByRole('button', { name: 'Task details status' })).toBeVisible();
});
