import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");
import Backend from "../../test_utils/backend.js";
import Activity from "../../test_utils/activity.js";

test.describe("INTEGRATION WITH BUDDYPRESS FEATURES", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
    });

    test("Enable media toggle and validate from the frontend", async ({ page, admin }) => {

        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-7']");
        await activity.gotoUserProfile();
        const profileSidebar = await page.locator("#member-primary-nav").textContent();
        expect(profileSidebar).toContain('Media');
    });
    test("Enable media in group toggle and validate from the frontend", async ({ page, admin }) => {
        await page.locator("//label[@for='rtmedia-album-enable']").check();
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-enable-on-group']");
        await page.goto(URLS.homepage + "/groups/create/step/group-details/");
        const groupTab = await page.locator("#group-create-tabs").textContent();
        expect(groupTab).toContain('Media');
    });

    test("Enable Allow upload from activity stream and validate from the frontend", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-bp-enable-activity']");
        await activity.gotoActivityPage();
        await page.locator("#whats-new").click();
        const postUpload = page.locator('#rtmedia-add-media-button-post-update');
        await expect(postUpload).toBeVisible();
    });

    test("Enable Create activity for media comments and validate from the frontend", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-enable-comment-activity']");
        const image = ['test-data/images/test.jpg'];
        await activity.gotoActivityPage();
        await activity.upploadMedia(image);
        await activity.clickedOnFirstPhotoOfTheActivityPage();
        await page.locator("//textarea[@id='comment_content']").fill("This is a test comment")
        await page.locator("#rt_media_comment_submit").click();
        await activity.gotoActivityPage();
        const commentActivity = await page.locator("//li[contains(@class, 'activity-item')]").first().textContent();
        expect(commentActivity).toContain("This is a test comment");
    });

    test("Enable Create activity for media Likes and validate from the frontend", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtmedia-enable-like-activity']");
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-display");
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-1']");
        const image = ['test-data/images/test.jpg'];
        await activity.gotoActivityPage();
        await activity.upploadMedia(image);
        await activity.clickedOnFirstPhotoOfTheActivityPage();
        await page.waitForLoadState("domcontentloaded");
        await page.locator("//div[@class='rtmedia-actions-before-comments clearfix']//span[contains(text(),'Like')]").click();
        await page.waitForSelector("//div[@class='rtmedia-actions-before-comments clearfix']//span[contains(text(),'Unlike')]");
        await activity.gotoActivityPage();
        const likeAcitivity = await page.locator("//li[contains(@class, 'activity-item')]").first().textContent();
        expect(likeAcitivity).toContain("liked");
    });
});