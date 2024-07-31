import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Activity from "../page_model/activity.js";

test.describe("Validating media size", () => {
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-custom-css-settings");
    });
    test("Validating custom css on the frontend", async ({ page }) => {
        const customCSS = "{background: red}"
        await page.locator('#rtmedia-custom-css').fill(customCSS);
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();

        await activity.gotoActivityPage();
        const actualCSS = await page.locator('#rtmedia-custom-css-inline-css').textContent();
        expect(actualCSS).toContain(customCSS);
    });
    
})