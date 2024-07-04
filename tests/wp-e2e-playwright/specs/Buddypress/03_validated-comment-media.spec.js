import { test, expect } from "@wordpress/e2e-test-utils-playwright";
const { URLS } = require("../../utils/urls.js");

test.describe("Validated comment media in the frontend", ()=>{
      test("Validated comment media in the frontend activity page", async({ page})=>{
        //validating allow upload on comment
        await page.goto(URLS.homepage +"/activity");
        await page.locator("#whats-new").fill("This is a demo post");
        await page.locator("#aw-whats-new-submit").click();
        await page.locator("//a[@class='button acomment-reply bp-primary-action bp-tooltip']").first().click();
        const commentUpload = page.locator("//span[@class='dashicons dashicons-admin-media']").first();
        await expect(commentUpload).toBeVisible();
    })
  })