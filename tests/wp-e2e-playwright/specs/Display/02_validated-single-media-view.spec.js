import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");

test.describe("Validated media view in the frontend", ()=>{
    test("Validate media search", async ({ page , admin }) => {
        await admin.visitAdminPage("/");
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click(); // clicked on the top profile link
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const mediaSearch = page.locator("#media_search_input")
        await expect(mediaSearch).toBeVisible();
    });
    test("validated lightbox to the display media", async ({ page , admin }) => {
        await page.goto(URLS.homepage +"/activity");
        await page.locator("//ul[contains(@class, 'rtm-activity-photo-list')]").first().click();
        const lightbox = page.locator("//div[@class='rtm-lightbox-container clearfix']");
        await expect(lightbox).toBeVisible();
    });
    test("validated lightbox to display media to the display media", async ({ page , admin }) => {
        await page.goto(URLS.homepage +"/activity");
        await page.locator("//ul[contains(@class, 'rtm-activity-photo-list')]").first().click();
        const lightbox = page.locator("//div[@class='rtm-lightbox-container clearfix']");
        await expect(lightbox).toBeVisible();
    });
    test("validated Media display pagination option to the display media", async ({ page , admin }) => {
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").click();
        const [ fileChooser ] = await Promise.all( [
          page.waitForEvent( 'filechooser' ),
          page.locator( '#rtmedia-add-media-button-post-update' ).click(),
        ] );
        await fileChooser.setFiles( [ 'uploads/img.jpg','uploads/img2.jpg','uploads/images.jpg']);
        await page.waitForTimeout(3000)
        admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("#rtm-form-number-0").fill("1");
        await page.locator("#rtm-form-radio-0").click();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        //validating load more option
        const loadMore = await page.locator('#rtMedia-galary-next').textContent();
        expect(loadMore).toContain("Load More");
        //validating pagination
        admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.locator("#rtm-form-radio-1").click();
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click();
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const pagination = page.locator("//div[@class='rtm-pagination clearfix']");

        //validating Masonry script present on the gallery or not
        const masonry = page.locator('.rtmedia-list-item.masonry-brick');
        await expect(masonry).toBeVisible();
    });

  })