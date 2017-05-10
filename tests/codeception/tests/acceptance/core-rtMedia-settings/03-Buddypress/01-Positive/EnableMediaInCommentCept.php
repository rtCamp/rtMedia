<?php

/**
* Scenario : To Check if the user is allowed to upload media in comment.
*/
    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $commentStr = 'This is the comment while uploading media.';

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( "Check if the user is allowed to upload media in comment" );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );

    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl ); // First we need to check if the user is allowed to cooment on upload media.
    $settings->verifyEnableStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );
    $settings->verifyEnableStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, ConstantsPage::$customCssTab ); //Last arg refers scroll postion

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp' );
    $I->wait( 5 );

    $settings->verifyEnableStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox ); //We need to check media is enabled for profile or not.
    $settings->verifyEnableStatus( ConstantsPage::$strMediaInCommnetLabel, ConstantsPage::$mediaInCommentCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );
    $temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

    $uploadmedia = new UploadMediaPage( $I );

    if( $temp >= ConstantsPage::$minValue ){

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $uploadmedia->fisrtThumbnailMedia();

        $I->seeElement( ConstantsPage::$commentLink );
        $I->scrollTo( ConstantsPage::$commentLink );
        $I->wait( 5 );

        $I->seeElement( UploadMediaPage::$commentTextArea );
        $I->fillfield( UploadMediaPage::$commentTextArea, $commentStr );

        $I->seeElement( ConstantsPage::$mediaButtonInComment );
        $I->attachFile( ConstantsPage::$uploadFileInComment, ConstantsPage::$imageName );
        $I->wait( 10 );

        $I->click( UploadMediaPage::$commentSubmitButton );
        $I->wait( 5 );
        $I->see( $commentStr );

    }else{

        $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
        $I->waitForElement( ConstantsPage::$displayTab , 10);
        $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox ); //This will check if the direct upload is disabled

        $buddypress->gotoMedia( ConstantsPage::$userName );
        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink);

        $I->reloadPage();
        $I->waitForElement( ConstantsPage::$profilePicture, 5 );

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $uploadmedia->fisrtThumbnailMedia();

        $I->seeElement( ConstantsPage::$commentLink );
        $I->scrollTo( ConstantsPage::$commentLink );
        $I->wait( 3 );

        $I->seeElement( UploadMediaPage::$commentTextArea );
        $I->fillfield( UploadMediaPage::$commentTextArea, $commentStr );

        $I->seeElement( ConstantsPage::$mediaButtonInComment );
        $I->attachFile( ConstantsPage::$uploadFileInComment, ConstantsPage::$imageName );
        $I->wait( 10 );

        $I->click( UploadMediaPage::$commentSubmitButton );
        $I->wait( 5 );
        $I->see( $commentStr );

    }

    $I->reloadPage();
    $logout = new LogoutPage( $I );
    $logout->logout();
?>
