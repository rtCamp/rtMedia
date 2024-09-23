import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../test_utils/backend.js";
import Activity from "../../test_utils/activity.js";

test.describe("Validated privacy settings", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-privacy");
    });
    test("Enable privacy settings and validated from the fronend", async ({ page }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-privacy-enable']");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-21']")
        //validated changes from the fronend
        await activity.gotoActivityPage();
        await page.locator("#whats-new").click();
        await page.waitForLoadState('domcontentloaded')
        const rtSelectPrivacy = page.locator("//select[@id='rtSelectPrivacy']");
        await expect(rtSelectPrivacy).toBeVisible({ timeout: 10000 });
        expect(rtSelectPrivacy).toBeTruthy(); 
    });
})