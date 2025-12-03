import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../test_utils/backend.js";

test.describe("Validate other settings", () => {
    let backend;
    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
    });

    test('Validate Admin bar menu integration', async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-22']")
        await page.waitForLoadState('domcontentloaded');
        const isrtMediaMenuBarVisible = (await page.isVisible('a[title="rtMedia"]'));
        expect(isrtMediaMenuBarVisible).toBeTruthy();
    })
});