import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Backend from "../../page_model/backend.js";
import Activity from "../../page_model/activity.js";
const { URLS } = require("../../utils/urls.js")

test.describe("Validated media view in the frontend", () => {
    let backend;
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        backend = new Backend(page);
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
    });

    test("Validate media search", async ({ page }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-2']");
        await activity.gotoUserProfile();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const mediaSearch = page.locator("#media_search_input")
        await expect(mediaSearch).toBeVisible();
    })

    test("Validated lightbox to display media", async ({ page }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-3']");
        const image = ['uploads/img.jpg'];
        await activity.upploadImages(image);
        await page.reload();
        await page.locator("//ul[contains(@class, 'rtm-activity-photo-list')]").first().click();

        const lightbox = "//div[contains(@class, 'rtm-lightbox-container')]"
        await page.waitForSelector(lightbox);
        await page.locator(lightbox).isVisible();
    })
    test("Validated Media display pagination option to the display media in profile", async ({ page, admin }) => {
        await backend.enableAnySettingAndSave("//label[@for='rtm-form-checkbox-4']");
        const imagesPath = ['uploads/img.jpg', 'uploads/img2.jpg', 'uploads/images.jpg'];
        await activity.upploadImages(imagesPath)
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("#rtm-form-number-0").fill("1");
        await page.locator("#rtm-form-radio-0").click();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        //validating load more in media album
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const loadMore = await page.locator('#rtMedia-galary-next').textContent();
        expect(loadMore).toContain("Load More");
        //validating pagination in media album
        admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await backend.enableAnySettingAndSave("#rtm-form-radio-1");
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const pagination = page.locator("//div[contains(@class, 'rtmedia_next_prev')]");
        await expect(pagination).toBeVisible();
        //validating Masonry script present on the gallery or not
        const masonry = page.locator('.rtmedia-list-item.masonry-brick');
        await expect(masonry).toBeVisible();
    })
});