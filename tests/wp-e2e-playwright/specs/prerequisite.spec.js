import { test} from "@wordpress/e2e-test-utils-playwright";
import Backend from "../test_utils/backend.js";

test.describe("Enable basic features to perform tests", () => {
    let backend;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-display");
    });

    test("Enable all of the prequisite", async ({ page, admin }) => {
        //enable direct upload
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-6']");
        //enable group from buddypress
        await admin.visitAdminPage("options-general.php?page=bp-components");
        await page.locator("//input[@id='bp_components[groups]']").check();
        await page.locator("//input[@id='bp-admin-component-submit']").click();
        //enable comment for media
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("//label[@for='rtm-form-checkbox-0']").check();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    });
    
});