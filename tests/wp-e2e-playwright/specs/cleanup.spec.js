import { test } from "@wordpress/e2e-test-utils-playwright";

test.describe("Cleanup - Disable all settings enabled in tests", () => {

    test("Disable all rtMedia settings that were enabled during tests", async ({ page, admin }) => {
        
        // ===== MAIN SETTINGS PAGE (Comment for media) =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable comments for media
        await page.locator("//label[@for='rtm-form-checkbox-0']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== DISPLAY SETTINGS =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-display");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable direct upload
        await page.locator("//label[@for='rtm-form-checkbox-6']").uncheck();
        
        // Disable likes for media
        await page.locator("//label[@for='rtm-form-checkbox-1']").uncheck();

        // Disable media search
        await page.locator("//label[@for='rtm-form-checkbox-2']").uncheck();
        // Disable lightbox
        await page.locator("//label[@for='rtm-form-checkbox-3']").uncheck();
        // Disable pagination
        await page.locator("//label[@for='rtm-form-checkbox-4']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== TYPES SETTINGS =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-types");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable Photo
        await page.locator("//label[@for='rtm-form-checkbox-11']").uncheck();
        
        // Disable Video
        await page.locator("//label[@for='rtm-form-checkbox-13']").uncheck();
        
        // Disable Music
        await page.locator("//label[@for='rtm-form-checkbox-15']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== GENERAL SETTINGS =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-general");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable Admin bar menu integration
        await page.locator("//label[@for='rtm-form-checkbox-22']").uncheck();
        
        // Disable Terms of Service settings
        await page.locator("//label[@for='rtm-form-checkbox-23']").uncheck();
        await page.locator("//label[@for='rtm-form-checkbox-24']").uncheck();
        
        // Disable Privacy message
        await page.locator("//label[@for='rtm-form-checkbox-25']").uncheck();
        
        // Disable Footer link
        await page.locator("//label[@for='rtm-form-checkbox-28']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== PRIVACY SETTINGS =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-privacy");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable privacy settings
        await page.locator("//label[@for='rtmedia-privacy-enable']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== BUDDYPRESS SETTINGS =====
        await admin.visitAdminPage("admin.php?page=rtmedia-settings#rtmedia-bp");
        await page.waitForLoadState('domcontentloaded');
        
        // Disable media toggle in profile
        await page.locator("//label[@for='rtm-form-checkbox-7']").uncheck();
        
        // Disable media in groups
        await page.locator("//label[@for='rtmedia-enable-on-group']").uncheck();
        
        // Disable Allow upload from activity stream
        await page.locator("//label[@for='rtmedia-bp-enable-activity']").uncheck();
        
        // Disable comment media
        await page.locator("//label[@for='rtm-form-checkbox-9']").uncheck();
        
        // Disable Create activity for media comments
        await page.locator("//label[@for='rtmedia-enable-comment-activity']").uncheck();
        
        // Disable Create activity for media Likes
        await page.locator("//label[@for='rtmedia-enable-like-activity']").uncheck();
        
        // Disable Albums
        await page.locator("//label[@for='rtmedia-album-enable']").uncheck();
        
        // Save settings
        await page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
        await page.waitForLoadState('domcontentloaded');

        // ===== BUDDYPRESS COMPONENTS =====
        // Disable BuddyPress Groups component
        await admin.visitAdminPage("options-general.php?page=bp-components");
        await page.waitForLoadState('domcontentloaded');
        
        await page.locator("//input[@id='bp_components[groups]']").uncheck();
        await page.locator("//input[@id='bp-admin-component-submit']").click();
        await page.waitForLoadState('domcontentloaded');
    });
});
