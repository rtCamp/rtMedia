<?php

/**
* Scenario : To disable upload media in comment.
*/
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( "To disable upload media in comment." );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );

    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl ); // First we need to check if the user is allowed to cooment on upload media.
    $settings->verifyEnableStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp' );
    $I->wait( 5 );

    $settings->verifyEnableStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox ); //We need to check media is enabled for profile or not.
    $settings->verifyDisableStatus( ConstantsPage::$strMediaInCommnetLabel, ConstantsPage::$mediaInCommentCheckbox );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
    $I->wait( 5 );
    $settings->verifyEnableStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );
    $temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

    $uploadmedia = new UploadMediaPage( $I );

    if( $temp >= ConstantsPage::$minValue ){

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $uploadmedia->fisrtThumbnailMedia();

        $I->seeElement( ConstantsPage::$commentLink );
        $I->scrollTo( ConstantsPage::$commentLink );
        $I->wait( 3 );

        $I->seeElement( UploadMediaPage::$commentTextArea );
        $I->fillfield( UploadMediaPage::$commentTextArea, 'This is comment when upload media is disabled' );

        $I->dontSeeElement( ConstantsPage::$mediaButtonInComment );

         $I->click( UploadMediaPage::$commentSubmitButton );
         $I->wait( 5 );

    }else{

        $I->amOnPage( '/wp-admin' );
        $I->waitForElement( LoginPage::$dashBoardMenu, 5 );

        $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
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
        $I->fillfield( UploadMediaPage::$commentTextArea, 'This is comment when upload media is disabled' );

        $I->dontSeeElement( ConstantsPage::$mediaButtonInComment );

        $I->click( UploadMediaPage::$commentSubmitButton );
        $I->wait( 5 );

    }

?>
