import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../page_model/backend.js";
import Activity from "../page_model/activity.js";

test.describe("Validated privacy settings", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-privacy");
    });
    test("Enable privacy settings and validated from the fronend", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("#rtmedia-privacy-enable");
        //validated changes from the fronend
        await activity.gotoActivityPage();
        await page.locator("#whats-new").click();
        const rtSelectPrivacy = page.locator("#rtSelectPrivacy");
        expect(rtSelectPrivacy).toBeVisible();
    });
})