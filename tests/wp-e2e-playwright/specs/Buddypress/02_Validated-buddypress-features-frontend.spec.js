import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");

test.describe("Validated all of the INTEGRATION WITH BUDDYPRESS FEATURES in the frontend", ()=>{

      test("Validated all of the settings in the frontend", async({ page})=>{
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click(); // clicked on the top profile link
        const profileSidebar = await page.locator("#member-primary-nav").textContent(); 
        //validating Enable media in profile
        expect(profileSidebar).toContain('Media');

        //validating Enable media in group
        await page.goto(URLS.homepage + "/groups/create/step/group-details/");
        const groupTab = await page.locator("#group-create-tabs").textContent();
        expect(groupTab).toContain('Media');

        //validating Allow upload from activity stream
        await page.goto(URLS.homepage+"/activity");
        await page.locator("#whats-new").click();
        const postUpload = page.locator('#rtmedia-add-media-button-post-update');
        await expect(postUpload).toBeVisible();

        //validating Create activity for media likes and comments
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").click();
        const [ fileChooser ] = await Promise.all( [
          page.waitForEvent( 'filechooser' ),
          page.locator( '#rtmedia-add-media-button-post-update' ).click(),
        ] );
        await fileChooser.setFiles( [ 'uploads/img.jpg' ]);
        await page.locator("//ul[@class='rtmedia-list rtm-activity-media-list rtmedia-activity-media-length-1 rtm-activity-photo-list']").first().click();
        await page.locator("#comment_content").fill("Test Comment");
        await page.locator("#rt_media_comment_submit").click();
        //validated comments activity
        await page.goto(URLS.homepage +"/activity");
        const firstAcitivity = await page.locator("//li[contains(@class, 'profile') and contains(@class, 'rtmedia_comment_activity') and contains(@class, 'activity-item')]").first().textContent();
        expect(firstAcitivity).toContain("Test Comment");
        //validated likes activity
        await page.locator("//ul[@class='rtmedia-list rtm-activity-media-list rtmedia-activity-media-length-1 rtm-activity-photo-list']").first().click();
        await page.locator("//button[@type='submit' and contains(@class, 'rtmedia-like')]").last().click();
        await page.goto(URLS.homepage +"/activity");
        const likeAcitivity = await page.locator("//li[contains(@class, 'profile') and contains(@class, 'rtmedia_like_activity') and contains(@class, 'activity-item')]").first().textContent();
        expect(likeAcitivity).toContain("liked");
      })
  })