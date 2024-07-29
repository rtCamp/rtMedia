import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");
import Backend from "../../page_model/backend.js";

test.describe("ASK USERS TO AGREE TO YOUR TERMS", ()=>{
    test("validated rtMedia footer link", async ({page, admin})=>{
        let backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-28']");
        await page.goto(URLS.homepage +"/activity");
        const hrefValue = await page.$eval('.rtmedia-footer-link a', (element) => {
            return element.getAttribute('href');
          });
        expect(hrefValue).toContain("https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media");
    })
})