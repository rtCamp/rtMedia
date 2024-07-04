import { test , expect} from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");
require( 'dotenv' ).config();

test.describe('Validating media notification', () => {
    test("Login test user and like any media", async ({ page }) => {
        await page.goto(URLS.homepage +"/wp-login.php");
        await page.locator("#user_login").fill(process.env.test_Username);
        await page.locator("#user_pass").fill(process.env.test_pass);
        await page.locator('#wp-submit').click();
        await page.goto(URLS.homepage +"/activity");
        await page.locator("//ul[@class='rtmedia-list rtm-activity-media-list rtmedia-activity-media-length-1 rtm-activity-photo-list']").first().click();
        await page.locator("#comment_content").fill("Test Comment");
        await page.locator("#rt_media_comment_submit").click();
    });

    test("Validate notification is received for media", async ({ page , admin }) => {
        await admin.visitAdminPage("/");
        await page.goto(URLS.homepage);
        await page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click(); // clicked on the top profile link
        await page.locator("#user-notifications").scrollIntoViewIfNeeded();
        await page.locator("#user-notifications").click();
        const firstNotification = await page.locator("//td[@class='notification-description']").first().textContent();
        expect(firstNotification).toContain("commented on");
    });
});