import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");
import Backend from "../../test_utils/backend.js";

test.describe("Validate footer link", () => {
    test("Validate rtMedia footer link", async ({ page, admin }) => {
        let backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-28']");
        await page.goto(URLS.homepage + "/activity");
        const footerDiv = page.locator(".rtmedia-footer-link");
        await footerDiv.scrollIntoViewIfNeeded();
        const footerLinkElement = footerDiv.locator("a");
        const footerLinkHref = await footerLinkElement.getAttribute('href');
        expect(footerLinkHref).toContain("https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media");
    })
})