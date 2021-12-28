import { loginUser } from '@wordpress/e2e-test-utils';

describe( 'Able to edit the album', () => { 
	it( 'should be able to edit the album', async () => {
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

     await page.click("body > div:nth-child(3) > div:nth-child(3) > div:nth-child(1) > main:nth-child(1) > article:nth-child(1) > div:nth-child(2) > div:nth-child(1) > div:nth-child(2) > div:nth-child(2) > div:nth-child(4) > ul:nth-child(5) > li:nth-child(1)");


    await page.waitForSelector("#rtm-media-options");
    await page.click(".clicker.rtmedia-action-buttons");

    await page.click("a[title='Edit Album']");


    await page.type("#media_title","update");
    await page.type("#description","desc")
    await page.click("input[value='Save Changes']");
    await page.waitForSelector(".rtmedia-container.rtmedia-single-container.rtmedia-media-edit");
      
    } );
    
} );

