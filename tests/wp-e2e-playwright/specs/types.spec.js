import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../test_utils/backend.js";
import Activity from "../test_utils/activity.js";

test.describe("Validate media types", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
    });
    test("Validate photo type by disbale it and check pop up", async ({admin }) => {
        await backend.disableAnySettingAndSave("//label[@for='rtm-form-checkbox-11']");
        await activity.gotoActivityPage();
        const image = ['test-data/images/test.jpg'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(image);
        expect(dialogMessage).toContain("File not supported. Allowed File Formats : mp4, mp3");
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-11']");
    });

    test("Validate video type by disbale it and check pop up message", async ({admin }) => {
        await backend.disableAnySettingAndSave("//label[@for='rtm-form-checkbox-13']");
        await activity.gotoActivityPage();
        const video = ['test-data/videos/testmpfour.mp4'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(video);
        expect(dialogMessage).toContain("File not supported. Allowed File Formats : jpg, jpeg, png, gif, mp3");
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-13']");
    });

    test("Validate Music type by disbale it and check pop up message", async ({admin }) => {
        await backend.disableAnySettingAndSave("//label[@for='rtm-form-checkbox-15']");
        await activity.gotoActivityPage();
        const video = ['test-data/music/mpthreetest.mp3'];
        const dialogMessage = await activity.getDialogMessageForInvalidFileUpload(video);
        expect(dialogMessage).toContain("File not supported. Allowed File Formats : jpg, jpeg, png, gif, mp4");
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-15']");
    });

   
})