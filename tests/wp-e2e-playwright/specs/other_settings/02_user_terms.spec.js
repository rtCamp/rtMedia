import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Activity from "../../page_model/activity.js";
import Backend from "../../page_model/backend.js";
const { URLS } = require("../../utils/urls.js");

test.describe("ASK USERS TO AGREE TO YOUR TERMS", () => {
    let activity;
    let backend;

    test.beforeEach(async ({ page, admin }) => {
        activity = new Activity(page);
        backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
    });

    test("Validated Link for Terms of Service page, Terms of Service Message and Error message", async ({ page, admin }) => {
        await page.locator("//label[@for='rtm-form-checkbox-23']").check();
        await page.locator("//label[@for='rtm-form-checkbox-24']").check();
        await page.locator("#rtm-form-text-0").fill("https://rtcamp.com");
        await page.locator("#rtm-form-text-1").fill("terms of services.");
        await page.locator("#rtm-form-text-2").fill("please check the terms");
        await backend.clickedOnrtMediaSave();

        await activity.gotoActivityPage();
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

        //disable all of the settings
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await page.locator("//label[@for='rtm-form-checkbox-23']").uncheck();
        await page.locator("//label[@for='rtm-form-checkbox-24']").uncheck();
        await backend.clickedOnrtMediaSave();
        })

    test("Validated Privacy messages", async ({ page, admin }) => {
        await page.locator("//label[@for='rtm-form-checkbox-25']").check();
        await page.locator("#rtm-form-textarea-0").fill("Demo Text");
        await backend.clickedOnrtMediaSave();
        await activity.gotoActivityPage();
        const errorMessage = await page.locator("div[class='privacy_message_wrapper'] p").textContent();
        expect(errorMessage).toContain("Demo Text");
    })
})