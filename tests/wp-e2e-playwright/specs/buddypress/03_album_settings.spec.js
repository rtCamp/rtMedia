import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../page_model/backend.js";
import Activity from "../../page_model/activity.js";

test.describe("Comment media BUDDYPRESS FEATURES", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
    });

    test("Validated Albums settings in the user's profile", async ({ page }) => {
        await backend.enableAnySettingAndSave("#rtmedia-album-enable");
        await activity.gotoUserProfile();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const Album = await page.locator("//ul[@class='subnav']").textContent();
        //validating Enable media in profile
        expect(Album).toContain('Albums');
    })
});