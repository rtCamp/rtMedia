import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../test_utils/backend.js";
import Activity from "../../test_utils/activity.js";

test.describe("Comment media BUDDYPRESS FEATURES", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
    });

    test("Validated Albums settings in the user's profile", async ({ page }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-album-enable']");
        await activity.gotoUserProfile();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const Album = await page.locator("//ul[@class='subnav']").textContent();
        expect(Album).toContain('Albums');
    })
});