<?php

/**
* Scenario : To set width of Music player for activity page.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;
    $scrollPos = ConstantsPage::$customCssTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of video player for activity page' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$activityPlayerLabel, ConstantsPage::$activityMusicWidthTextbox, ConstantsPage::$activityMusicPlayerWidth, $scrollPos );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
    $I->wait( 5 );
    $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, $scrollToDirectUpload );
    $settings->verifyDisableStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, ConstantsPage::$customCssTab );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp' );
    $I->wait( 5 );
    $settings->verifyEnableStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-types' );
    $I->wait( 5 );
    $settings->verifyEnableStatus( ConstantsPage::$musicLabel, ConstantsPage::$musicCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$audioName );

    echo $I->grabAttributeFrom( ConstantsPage::$audioSelectorActivity, 'style' );

    $logout = new LogoutPage( $I );
    $logout->logout();
?>
