import { test } from "@wordpress/e2e-test-utils-playwright";

test.describe("Validated all of the buddypress settings from backend and frontend as well", ()=>{
    test.beforeEach(async ({admin }) => {
        await admin.visitAdminPage("/");
      });
      test('Enable all of the settings of buddypress and save the changes', async({ page, admin})=>{
        //visit rtCamp buddypress setting
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
        //enable all of the buddypress settings 
        await page.locator("#rtm-form-checkbox-7").check();
        await page.locator("#rtmedia-enable-on-group").check();
        await page.locator("#rtmedia-bp-enable-activity").check();
        await page.locator("#rtm-form-checkbox-8").check();
        await page.locator("#rtmedia-enable-like-activity").check();
        await page.locator("#rtmedia-enable-comment-activity").check();
        
        //Enable media in comment
        await page.locator("#rtm-form-checkbox-9").check();
        await page.locator("#rtm-form-checkbox-10").check();
        //enable ALBUM SETTINGS
        await page.locator("#rtmedia-album-enable").check();
        //Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
      })
  })