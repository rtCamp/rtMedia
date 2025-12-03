import { test} from "@wordpress/e2e-test-utils-playwright";
import Backend from "../test_utils/backend.js";

test.describe("Enable basic features to perform tests", () => {
    let backend;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-display");
    });

    test("Enable all of the prerequisite", async ({ page, admin }) => {
        // Enable direct upload
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-6']");
        
        // Enable Photo, Video, and Music types
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        // Photo
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-11']");
        // Video
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-13']");
        // Music
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-15']");


        // Enable media in profile and attach media to activity
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-7']");
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-bp-enable-activity']");

        // Enable group from buddypress
        await admin.visitAdminPage("options-general.php?page=bp-components");
        await page.locator("//input[@id='bp_components[groups]']").check();
        await page.locator("//input[@id='bp-admin-component-submit']").click();
        
        // Enable comment for media
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("//label[@for='rtm-form-checkbox-0']").check();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    });
    
});