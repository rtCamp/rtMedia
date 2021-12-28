import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'Upload media from activity', () => { 
	it( ' should upload media from activity successfully', async () => {
       await loginUser();
       await page.click("body > div:nth-child(3) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)")
       const url = page.url();
       await page.goto(url + "/activity");
       await page.click("#whats-new");
       await page.type("#whats-new", "test");
       
      const elementHandle = await page.$("button[id='rtmedia-add-media-button-post-update'] span[class='dashicons dashicons-admin-media']");

      await elementHandle.uploadFile('download.png');
       await page.click("#aw-whats-new-submit");

       await page.waitForSelector(".activity-list.bp-list .activity-item");
       
    } );
    
} );

