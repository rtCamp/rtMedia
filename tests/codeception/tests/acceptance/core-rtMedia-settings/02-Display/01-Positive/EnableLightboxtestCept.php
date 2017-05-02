<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if the lightbox is enabled' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, ConstantsPage::$customCssTab ); //Last arg refers scroll postion

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage($I);
    $temp = $buddypress->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    if( $temp >= ConstantsPage::$minValue ){

        $I->scrollTo( '.rtm-gallery-title' );

        $uploadmedia->fisrtThumbnailMedia();

        $I->seeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox
        $I->click( ConstantsPage::$closeButton );

    }else{

        $I->amOnPage('/wp-admin');
        $I->wait( 10 );

        $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
        $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox ); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

        $I->reloadPage();
        $I->wait( 7 );

        $uploadmedia->fisrtThumbnailMedia();

        $I->seeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox
        $I->click( ConstantsPage::$closeButton );

    }

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
