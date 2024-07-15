import {test } from "@wordpress/e2e-test-utils-playwright";

test.describe("Validate the display settings", ()=>{
    test.beforeEach(async ({admin }) => {
        admin.visitAdminPage("admin.php?page=rtmedia-settings");
      });
    test('Enable all of the toggle from the display settings', async ({page, admin})=>{
        await page.locator("#rtm-form-checkbox-0").check();
        await page.locator("#rtm-form-checkbox-1").check();
        await page.locator("#rtm-form-checkbox-2").check();
        await page.locator("#rtm-form-checkbox-3").check();
        await page.locator("#rtm-form-checkbox-4").check();
        await page.locator("#rtm-form-checkbox-5").check();
        await page.locator("#rtm-form-checkbox-6").check();
        await page.waitForTimeout(2000);
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    })
})