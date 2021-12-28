import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'validate media privacy', () => { 
	it( 'enable media privacy', async () => {
       await loginUser();
       await page.click("#toplevel_page_rtmedia-settings");
       await page.waitForSelector("#bp_media_settings_form");
       await page.waitForSelector("#rtm-settings-tabs");
       await page.click("#tab-rtmedia-privacy");
       const element = await page.$("#rtmedia-privacy-enable");
       const isCheckBoxChecked = await (await element.getProperty("checked")).jsonValue();
        if(! isCheckBoxChecked ){
          await element.click()
        }
        
       await page.click("div[class='rtm-button-container top'] input[value='Save Settings']");
       await page.waitForSelector(".rtm-success.rtm-fly-warning.rtm-save-settings-msg");
    } );

    it( 'enable media privacy', async () => {
        await loginUser();
        await page.click("body > div:nth-child(3) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)")
        const url = page.url();
        await page.goto(url + "/activity");
        await page.click("#whats-new");
        await page.type("#whats-new", "test");
        
       const elementHandle = await page.$("button[id='rtmedia-add-media-button-post-update'] span[class='dashicons dashicons-admin-media']");
 
       await elementHandle.uploadFile('download.png');

       await page.select("select#rtSelectPrivacy", "60");
        await page.click("#aw-whats-new-submit");
 
       
     } );
    
} );

