import { test} from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../page_model/backend.js";

test.describe("INTEGRATION WITH BUDDYPRESS FEATURES", () => {
    let backend;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-display");
    });

    test("Enable media toggle and validate from the frontend", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-6']");
    });
    
});