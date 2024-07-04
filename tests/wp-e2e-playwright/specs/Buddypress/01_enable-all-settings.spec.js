import { test } from "@wordpress/e2e-test-utils-playwright";

test.describe("Validated all of the buddypress settings from backend and frontend as well", ()=>{
    test.beforeEach(async ({admin }) => {
        await admin.visitAdminPage("/");
      });
      test('Enable all of the settings of buddypress and save the changes', async({ page, admin})=>{
        //visit rtCamp buddypress setting
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
        //enable all of the buddypress settings 
        await page.locator("label[for='rtm-form-checkbox-7'] span[class='switch-label']").check();
        await page.locator("label[for='rtmedia-enable-on-group'] span[class='switch-label']").check();
        await page.locator("label[for='rtmedia-bp-enable-activity'] span[class='switch-label']").check();
        await page.locator("label[for='rtm-form-checkbox-8'] span[class='switch-label']").check();
        await page.locator("label[for='rtmedia-enable-like-activity'] span[class='switch-label']").check();
        await page.locator("label[for='rtmedia-enable-comment-activity'] span[class='switch-label']").check();
        
        //Enable media in comment
        await page.locator("label[for='rtm-form-checkbox-9'] span[class='switch-label']").check();
        await page.locator("label[for='rtm-form-checkbox-10'] span[class='switch-label']").check();
        //enable ALBUM SETTINGS
        await page.locator("label[for='rtmedia-album-enable'] span[class='switch-label']").check();
        //Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
      })
  })