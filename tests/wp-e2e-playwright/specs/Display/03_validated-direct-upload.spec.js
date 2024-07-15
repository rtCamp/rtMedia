import {test,expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");

test.describe("Validate direct upload", ()=>{
    test.beforeEach(async ({page, admin }) => {
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("#rtm-form-checkbox-6").uncheck();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
      });

    test('Validated direct upload disable', async ({page, admin})=>{
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").click();
        const [ fileChooser ] = await Promise.all( [
          page.waitForEvent( 'filechooser' ),
          page.locator( '#rtmedia-add-media-button-post-update' ).click(),
        ] );
        await fileChooser.setFiles( [ 'uploads/img.jpg' ]);
        await page.waitForTimeout(3000);
        const uploadedMedia = page.locator("//li[@class='plupload_file ui-state-default plupload_queue_li']");
        await expect(uploadedMedia).toBeVisible();
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("#rtm-form-checkbox-6").uncheck();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
      });
})