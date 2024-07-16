import { test, expect } from "@wordpress/e2e-test-utils-playwright";

test.describe("Validated ADMIN SETTINGS", ()=>{
    test('Validated Admin bar menu integration', async ({page, admin})=>{
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await page.waitForLoadState();
        await page.locator("//label[@for='rtm-form-checkbox-22']").check();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        
        const isrtMediaMenuBarVisible = (await page.isVisible('a[title="rtMedia"]'));
        expect(isrtMediaMenuBarVisible).toBeTruthy();

        await page.locator("//label[@for='rtm-form-checkbox-22']").uncheck();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();

        const isrtMediaMenuBarHidden = !(await page.isVisible('a[title="rtMedia"]'));
        expect(isrtMediaMenuBarHidden).toBeTruthy();
        
        
    })



})