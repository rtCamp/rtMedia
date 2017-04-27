<?php

/**
* Scenario :Allow upload for photo media types.
*/
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Allow upload for photo media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$typesTab, ConstantsPage::$typesTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp' );
    $I->wait( 5 );
    $settings->verifyEnableStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox ); //It will check if thr upload from activity is enabled from back end.

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName );

    $I->reloadPage();
    $I->waitForElement( ConstantsPage::$profilePicture, 5 );
    
    $I->seeElementInDOM( 'li.rtmedia-list-item.media-type-photo' );
    echo nl2br( "Photo is uploaded.. \n" );

?>
