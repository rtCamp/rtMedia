import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'Comment on uploaded media', () => { 

    it( 'enable comment from settings', async () => {
        await loginUser();
        await page.click("#toplevel_page_rtmedia-settings");
        await page.waitForSelector("#bp_media_settings_form");
        await page.waitForSelector("#rtm-settings-tabs");
        const element = await page.$("#rtm-form-checkbox-0");
        const isCheckBoxChecked = await (await element.getProperty("checked")).jsonValue();
         if(! isCheckBoxChecked ){
           await element.click()
         }
   
         await page.click("div[class='rtm-button-container top'] input[value='Save Settings']");
        await page.waitForSelector(".rtm-success.rtm-fly-warning.rtm-save-settings-msg");
     } );

	it( 'should able to add the media from comment', async () => {
       await loginUser();
       await page.click("body > div:nth-child(3) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)")
       const url = page.url();
       await page.goto(url + "/activity");

       await page.waitForSelector(".rtmedia-activity-container");
       await page.waitForSelector(".rtmedia-list.rtm-activity-media-list.rtmedia-activity-media-length-1.rtm-activity-photo-list");
       await page.click(".rtmedia-list-item.media-type-photo");
       await page.waitForSelector(".rtmedia-item-comments");

       await page.type('#comment_content','comment');
       const elementHandle = await page.$(".rtmedia-comment-media-upload");

       await elementHandle.uploadFile('download.png');

       await page.click("#rt_media_comment_submit");
       await page.waitForSelector(".rtmedia-comment");
       
    } );
    
} );

