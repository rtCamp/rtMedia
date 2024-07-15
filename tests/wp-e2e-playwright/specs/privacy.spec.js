import { expect, test } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../utils/urls.js");

test.describe("Validated privacy settings", () => {
    test.beforeEach(async ({ admin }) => {
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-privacy");
    });

    test("Enable privacy settings and validated from the fronend", async ({ page, admin }) => {
        await page.locator("#rtmedia-privacy-enable").check();
        await page.locator("#rtm-form-radio-3").check();
        await page.locator("#rtm-form-checkbox-21").check();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();

        //validated changes from the fronend
        await page.goto(URLS.homepage + "/activity");
        await page.locator("#whats-new").click();
        const rtSelectPrivacy = page.locator("#rtSelectPrivacy");
        expect(rtSelectPrivacy).toBeVisible();

        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-privacy");
        await page.locator("#rtmedia-privacy-enable").uncheck();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    });
})