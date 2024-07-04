import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");

test.describe("Validated Albums settings in the frontend", ()=>{
      test("Validated Albums settings in the user's profile", async({ page})=>{
        await page.goto(URLS.homepage);
        //Validating ALBUM SETTINGS
        await page.getByRole('navigation', { name: 'Main menu' }).getByRole('link', { name: 'test' }).click(); // clicked on the top profile link
        await page.locator("#user-media").scrollIntoViewIfNeeded();
        await page.locator("#user-media").click();
        const Album = await page.locator("//ul[@class='subnav']").textContent();
        //validating Enable media in profile
        expect(Album).toContain('Albums');
      })
  })