import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'Able to create a album', () => { 
	it( 'should be able to create a album', async () => {
       await loginUser();
       await page.click("body > div:nth-child(3) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)")
       const url = page.url();
       await page.goto(url + "/members");

       await page.waitForSelector(".screen-content");
       await page.waitForSelector("#members-list");
       await page.waitForSelector(".item-entry.bp-single-member.is-online.is-current-user");
       await page.click(".list-title.member-name");

       await page.waitForSelector("#object-nav");
       await page.click("#media-personal-li");
       
     await page.click("#rtmedia-nav-item-albums");

    await page.waitForSelector("#rtm-media-options");
    await page.click(".clicker.rtmedia-action-buttons");

    await page.click("a[title='Create New Album']");

    await page.type("#rtmedia_album_name","test");
    await page.click("#rtmedia_create_new_album");
    await page.waitForSelector(".rtmedia-success.rtmedia-create-album-alert");
    await page.click("button[title='Close (Esc)']");
    await page.waitForSelector(".rtmedia-list-media.rtmedia-list.rtmedia-album-list.clearfix");
       
    } );
    
} );

