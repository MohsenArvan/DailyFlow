import { randomUUID } from 'node:crypto';
import { expect, test, type Page } from '@playwright/test';

async function openProject(page: Page) {
    await page.goto('/register');
    await page.locator('#name').fill('Notes Tester');
    await page.locator('#email').fill(randomUUID() + '@example.test');
    await page.locator('#password').fill('DailyFlow-test-2026!');
    await page.locator('#passwordConfirmation').fill('DailyFlow-test-2026!');
    await page.locator('button[type=submit]').click();
    await expect(page).toHaveURL(/\/today$/);
    await page.goto('/projects');
    await page.getByRole('button', { name: /پروژه جدید/ }).first().click();
    const dialog = page.getByRole('dialog');
    await dialog.getByPlaceholder(/مثلاً: طراحی/).fill('پروژهٔ آزمایشی');
    await dialog.locator('button[type=submit]').click();
    await page.locator('aside').getByText('پروژهٔ آزمایشی', { exact: true }).first().click();
    await expect(page).toHaveURL(/\/projects\/[a-f0-9-]+$/);
}

async function createNote(page: Page, title: string, body: string) {
    await page.getByRole('button', { name: 'یادداشت جدید' }).click();
    const editor = page.getByRole('dialog', { name: 'یادداشت جدید' });
    await editor.getByLabel('عنوان').fill(title);
    await editor.getByLabel('متن یادداشت').fill(body);
    await editor.getByRole('button', { name: 'ذخیره' }).click();
    await expect(page.getByRole('button', { name: title, exact: true })).toBeVisible();
}

test('project notes persist and keep the existing list and board usable', async ({ page }) => {
    await openProject(page);
    const tasks = page.getByRole('tab', { name: 'تسک‌ها', exact: true });
    const notes = page.getByRole('tab', { name: 'یادداشت‌ها', exact: true });
    await expect(tasks).toHaveAttribute('aria-selected', 'true');
    const taskInput = page.getByPlaceholder(/افزودن تسک جدید/);
    await taskInput.fill('تسک پروژه');
    await taskInput.press('Enter');
    await expect(page.getByRole('button', { name: 'تسک پروژه', exact: true })).toBeVisible();

    await notes.click();
    await expect(page).toHaveURL(/\?tab=notes$/);
    await expect(page.getByText('هنوز یادداشتی برای این پروژه ثبت نشده')).toBeVisible();
    await createNote(page, 'مشکلات موبایل', 'خط اول\nخط دوم');
    await createNote(page, 'بررسی دیگر', 'متن دوم');
    await page.reload();
    await expect(notes).toHaveAttribute('aria-selected', 'true');

    await page.getByRole('button', { name: 'مشکلات موبایل', exact: true }).click();
    const reader = page.getByRole('dialog', { name: 'مشکلات موبایل' });
    await expect(reader).toContainText('خط دوم');
    await page.keyboard.press('Escape');
    await expect(page.getByRole('button', { name: 'مشکلات موبایل', exact: true })).toBeFocused();

    const actions = page.getByRole('button', { name: 'گزینه‌های مشکلات موبایل' });
    await actions.click();
    await page.getByRole('button', { name: 'ویرایش', exact: true }).click();
    const editor = page.getByRole('dialog', { name: 'ویرایش یادداشت' });
    await editor.getByLabel('متن یادداشت').fill('متن اصلاح‌شده');
    await editor.getByRole('button', { name: 'ذخیره' }).click();
    await expect(page.getByText('متن اصلاح‌شده')).toBeVisible();

    await actions.click();
    await page.getByRole('button', { name: 'سنجاق کردن', exact: true }).click();
    await expect(page.locator('article').first()).toContainText('مشکلات موبایل');
    await expect(page.getByLabel('سنجاق‌شده', { exact: true })).toBeVisible();
    await actions.click();
    await page.getByRole('button', { name: 'حذف', exact: true }).click();
    await page.getByRole('dialog', { name: 'حذف یادداشت' }).getByRole('button', { name: 'حذف', exact: true }).click();
    await expect(page.getByRole('button', { name: 'مشکلات موبایل', exact: true })).toHaveCount(0);

    await tasks.click();
    await page.getByRole('button', { name: 'کانبان', exact: true }).click();
    await expect(page.getByRole('region', { name: 'در انتظار', exact: true })).toContainText('تسک پروژه');
    await notes.click();
    await page.goBack();
    await expect(tasks).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByRole('region', { name: 'در انتظار', exact: true })).toContainText('تسک پروژه');
});

test('mobile editor keeps the draft after a failed save and a cancelled navigation', async ({ page }) => {
    await openProject(page);
    await page.getByRole('tab', { name: 'یادداشت‌ها' }).click();
    await page.setViewportSize({ width: 390, height: 844 });
    await page.route('**/api/v1/projects/*/notes', async route => {
        if (route.request().method() !== 'POST') return route.continue();
        await route.fulfill({ status: 500, contentType: 'application/json', body: '{}' });
    });

    const trigger = page.getByRole('button', { name: 'یادداشت جدید' });
    await trigger.click();
    const editor = page.getByRole('dialog', { name: 'یادداشت جدید' });
    await editor.getByLabel('عنوان').fill('پیش‌نویس');
    await editor.getByLabel('متن یادداشت').fill('متن باید بماند');
    await editor.getByRole('button', { name: 'ذخیره' }).click();
    await expect(editor.getByRole('alert')).toBeVisible();
    await expect(editor.getByLabel('متن یادداشت')).toHaveValue('متن باید بماند');

    page.once('dialog', dialog => dialog.dismiss());
    await page.goBack();
    await expect(page).toHaveURL(/\?tab=notes$/);
    await expect(editor.getByLabel('متن یادداشت')).toHaveValue('متن باید بماند');

    page.once('dialog', dialog => dialog.accept());
    await editor.getByRole('button', { name: 'انصراف' }).click();
    await expect(editor).toBeHidden();
    await expect(trigger).toBeFocused();
});

test('notes stay accessible through the keyboard when project tasks fail to load', async ({ page }) => {
    await page.route(/\/api\/v1\/tasks\?project_id=/, route =>
        route.fulfill({ status: 500, contentType: 'application/json', body: '{}' }),
    );
    await openProject(page);

    const tasks = page.getByRole('tab', { name: 'تسک‌ها', exact: true });
    const notes = page.getByRole('tab', { name: 'یادداشت‌ها', exact: true });
    await expect(page.getByText('دریافت تسک‌ها ممکن نشد.')).toBeVisible();
    await tasks.focus();
    await page.keyboard.press('ArrowLeft');
    await expect(notes).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(page.getByText('هنوز یادداشتی برای این پروژه ثبت نشده')).toBeVisible();

    const direct = page.url();
    await page.goto(direct);
    await expect(notes).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByRole('button', { name: 'یادداشت جدید' })).toBeVisible();
});
