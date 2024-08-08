import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Activity from "../test_utils/activity.js";
const testdata = require("../test-data/media_size/media_size.json")

test.describe("Validating media size", () => {
    let activity;

    test.beforeEach(async ({ page, admin }) => {
        activity = new Activity(page);
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-sizes");
    });
    test("Enter photo size in the backend and validated on frontend", async ({ page }) => {
        await page.locator("#rtm-form-number-1").fill(testdata.photo.thumbnailWidth);
        await page.locator("#rtm-form-number-2").fill(testdata.photo.thumbnailHeight);
        await page.locator("#rtm-form-number-3").fill(testdata.photo.mediumWidth);
        await page.locator("#rtm-form-number-4").fill(testdata.photo.mediumHeight);
        await page.locator("#rtm-form-number-5").fill(testdata.photo.largeWidth);
        await page.locator("#rtm-form-number-6").fill(testdata.photo.largeHeight);
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        
        const imagepath = ['test-data/images/test.jpg'];
        await activity.gotoActivityPage();
        await activity.upploadMedia(imagepath);
        await activity.gotoUserProfile();
        await page.locator("#user-media").click();
        await page.waitForLoadState('domcontentloaded');
        const expectedThumbnail = testdata.photo.thumbnailWidth + "x" + testdata.photo.thumbnailHeight;
        //validating thumbnail size of the photo
        expect(await activity.getPhotoSize()).toContain(expectedThumbnail);

        //validating medium photo size
        const expectedMediumSize = testdata.photo.mediumWidth + "x" + testdata.photo.mediumHeight;
        await activity.gotoActivityPage()
        expect(await activity.getPhotoSize()).toContain(expectedMediumSize);

        //validting large Photo size
        await activity.clickedOnFirstPhotoOfTheActivityPage();
        const imgLocator = page.locator('div.rtmedia-media img');
        const srcValue = await imgLocator.getAttribute('src');
        const expectedLargeSize = testdata.photo.largeWidth + "x" + testdata.photo.largeHeight;
        expect(srcValue).toContain(expectedLargeSize);
    });

    test("Validate video size", async ({page})=>{
        await page.locator("#rtm-form-number-7").fill(testdata.video.activityPlayerWidth);
        await page.locator("#rtm-form-number-8").fill(testdata.video.activityPlayerHeight);
        await page.locator("#rtm-form-number-9").fill(testdata.video.singlePlayerWidth);
        await page.locator("#rtm-form-number-10").fill(testdata.video.singlePlayerHeight);
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();

        const videoPath = ['test-data/videos/testmpfour.mp4'];
        await activity.gotoActivityPage();
        await activity.upploadMedia(videoPath);
        await page.waitForLoadState('domcontentloaded');
        const actualSize = await page.locator("//div[contains(@class, 'mejs-overlay') and contains(@class, 'mejs-layer') and contains(@class, 'mejs-overlay-play')]").first().getAttribute('style');
        const expectedSize = "width: "+testdata.video.activityPlayerWidth+"px; height: "+testdata.video.activityPlayerHeight+"px";
        expect(actualSize).toContain(expectedSize);
    })
    
})