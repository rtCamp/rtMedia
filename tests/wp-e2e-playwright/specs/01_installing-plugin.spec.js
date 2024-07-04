const { test, expect } = require("@wordpress/e2e-test-utils-playwright");

test.describe("Validate the plugin installation", ()=> {
  test.beforeEach(async ({admin }) => {
        await admin.visitAdminPage("/");
      });

    test("Installed plugin", async ({ page, admin }) => {
        const rtMediaDataSlug = "//a[@data-slug='buddypress-media']";
        await admin.visitAdminPage( '/plugin-install.php?s=rtmedia&tab=search&type=term');
        try {
          const pluginState = await page.locator(rtMediaDataSlug).textContent();
        if (pluginState == "Activate") {
          await page.locator(rtMediaDataSlug).click();
      }
        else if (pluginState == "Install Now") {
            await page.locator(rtMediaDataSlug).click();
            await page.waitForTimeout(15000);  // Wait for 15 seconds
            await page.reload();  // Reload the page
            await page.locator(rtMediaDataSlug).click();
      }
      
        } catch (error) {
          console.log('Plugin already activated')
          await admin.visitAdminPage("/")
        }
        
      })
     

    test("Validate plugin is installed successfully", async ( { page}) =>{
        const adminMenu = await page.locator("#adminmenu").textContent(); 
        expect(adminMenu).toContain('rtMedia');
      })
});