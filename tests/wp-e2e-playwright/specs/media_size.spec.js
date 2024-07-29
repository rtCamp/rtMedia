import { test, expect } from "@wordpress/e2e-test-utils-playwright";
import Activity from "../page_model/activity.js";
const testdata = require("../testdata/media_size.json")

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

        const image = ['testdata/largePhoto.jpg'];
        await activity.upploadImages(image);
        await page.waitForTimeout(5000);
        await activity.gotoUserProfile();
        await page.locator("#user-media").click();
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
        await page.waitForTimeout(5000);
    });
})