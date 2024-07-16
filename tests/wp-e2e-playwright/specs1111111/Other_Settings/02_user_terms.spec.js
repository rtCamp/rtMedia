import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import { ExportOptionsFromJSON } from "mailslurp-client";
const { URLS } = require("../../utils/urls.js");

test.describe("ASK USERS TO AGREE TO YOUR TERMS", ()=>{
    test("Enable all the settings of terms", async({page,admin})=>{
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await page.locator("//label[@for='rtm-form-checkbox-23']").check();
        await page.locator("#rtm-form-checkbox-24").check();
        await page.locator("#rtm-form-text-0").fill("https://rtcamp.com");
        await page.locator("#rtm-form-text-1").fill("terms of services.");
        await page.locator("#rtm-form-text-2").fill("please check the terms");
        await page.locator("#rtm-form-checkbox-25").check();
        await page.locator("#rtm-form-textarea-0").fill("Demo Text");
        await page.locator("#rtm-form-checkbox-28").check();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    })

    test("Show 'Terms of Service' checkbox on upload screen", async({page,admin})=>{
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").click();
        const termCheckBox = page.locator('#rtmedia_upload_terms_conditions');
        expect(termCheckBox).toBeVisible();
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await page.locator("//label[@for='rtm-form-checkbox-23']").uncheck();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    })

    test("Validated Link for Terms of Service page, Terms of Service Message and Error message", async({page,admin})=>{
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").click();

        //Link for "Terms of Service" page
        const hrefValue = await page.$eval('.rtmedia-upload-terms label a', (element) => {
            return element.getAttribute('href');
          });
        expect(hrefValue).toContain('https://rtcamp.com');

        //Terms of Service Message
        const termCheckBox = await page.locator("//label[@for='rtmedia_upload_terms_conditions']").textContent();
        expect(termCheckBox).toContain("terms of services.")

        //check error messages
        await page.locator('#rtmedia_upload_terms_conditions').check();
        await page.locator('#rtmedia_upload_terms_conditions').uncheck();
        const errorMessage = await page.locator(".rt_alert_msg").textContent();
        expect(errorMessage).toContain("please check the terms");
    })

    test("Validated error messages", async({page,admin})=>{
        await page.goto(URLS.homepage +"/activity");
        const errorMessage = await page.locator("div[class='privacy_message_wrapper'] p").textContent();
        expect(errorMessage).toContain("Demo Text");
    })

    test("validated rtMedia footer link", async ({page })=>{
        await page.goto(URLS.homepage +"/activity");
        const hrefValue = await page.$eval('.rtmedia-footer-link a', (element) => {
            return element.getAttribute('href');
          });
        expect(hrefValue).toContain("https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media");
    
    })
})