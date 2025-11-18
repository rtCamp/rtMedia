import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../test_utils/backend.js";
import Activity from "../test_utils/activity.js";

test.describe("Validate media types", () => {
    let backend;
    let activity;
    // Variable to track which setting needs to be re-enabled
    let selectorToReset = null;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        selectorToReset = null; // Reset tracker
    });

    test.afterEach(async ({ admin }) => {
        if (selectorToReset) {
            await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
            await backend.enableAnySettingAndSave(selectorToReset);
        }
    });

    test("Validate photo type by disabling it and checking popup", async ({ admin }) => {
        selectorToReset = "//label[@for='rtm-form-checkbox-11']";
        await backend.disableAnySettingAndSave(selectorToReset);
        
        await activity.gotoActivityPage();
        const image = ['test-data/images/test.jpg'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(image);
        
        expect(dialogMessage).toContain("File not supported. Allowed File Formats :");
        expect(dialogMessage).toContain("mp4");
        expect(dialogMessage).toContain("mp3");
        expect(dialogMessage).not.toContain("jpg");
    });

    test("Validate video type by disabling it and checking popup message", async ({ admin }) => {
        selectorToReset = "//label[@for='rtm-form-checkbox-13']";
        await backend.disableAnySettingAndSave(selectorToReset);
        
        await activity.gotoActivityPage();
        const video = ['test-data/videos/testmpfour.mp4'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(video);
        
        expect(dialogMessage).toContain("File not supported. Allowed File Formats :");
        expect(dialogMessage).toContain("jpg");
        expect(dialogMessage).toContain("mp3");
        expect(dialogMessage).not.toContain("mp4");
    });

    test("Validate Music type by disabling it and checking popup message", async ({ admin }) => {
        selectorToReset = "//label[@for='rtm-form-checkbox-15']";
        await backend.disableAnySettingAndSave(selectorToReset);
        
        await activity.gotoActivityPage();
        const music = ['test-data/music/mpthreetest.mp3'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(music);
        
        expect(dialogMessage).toContain("File not supported. Allowed File Formats :");
        expect(dialogMessage).toContain("jpg");
        expect(dialogMessage).toContain("mp4");
        expect(dialogMessage).not.toContain("mp3");
    });
});