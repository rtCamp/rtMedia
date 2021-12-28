import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'Like media from activity', () => { 
	it( 'should able to like the media from activity', async () => {
       await loginUser();
       await page.click("body > div:nth-child(3) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)")
       const url = page.url();
       await page.goto(url + "/activity");

       await page.waitForSelector(".rtmedia-activity-container");
       await page.waitForSelector(".rtmedia-list.rtm-activity-media-list.rtmedia-activity-media-length-1.rtm-activity-photo-list");
       await page.click(".rtmedia-list-item.media-type-photo");
       await page.waitForSelector(".rtmedia-item-comments");
       await page.click("div[class='rtmedia-actions-before-comments clearfix'] button[title='Like']");
       await page.waitForSelector(".rtmedia-like-info");
       
    } );
    
} );

