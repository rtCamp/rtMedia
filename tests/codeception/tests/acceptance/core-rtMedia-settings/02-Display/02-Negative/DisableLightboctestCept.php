<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if the lightbox is disabled' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, ConstantsPage::$customCssTab );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage($I);
    $temp = $buddypress->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    if( $temp >= ConstantsPage::$minValue ){

        $I->scrollTo( '.rtm-gallery-title' );

        $uploadmedia->fisrtThumbnailMedia();
        $I->dontSeeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox

    }else{

        $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
        $I->waitForElement( ConstantsPage::$displayTab , 10);
        $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox ); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

        $I->reloadPage();
        $I->wait( 7 );

        $uploadmedia->fisrtThumbnailMedia();
        $I->dontSeeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox

    }

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
