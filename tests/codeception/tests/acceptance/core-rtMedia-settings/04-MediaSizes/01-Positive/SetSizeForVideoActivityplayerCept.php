<?php

/**
 * Scenario : To set height and width of video player for activity page.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of video player for activity page' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$activityPlayerLabel, ConstantsPage::$activityVideoWidthTextbox, ConstantsPage::$activityVideoPlayerWidth, ConstantsPage::$activityVideoHeightTextbox, ConstantsPage::$activityVideoPlayerHeight );

    $settings->enableRequestedMediaTypes( ConstantsPage::$videoLabel, ConstantsPage::$videoCheckbox );

    $settings->enableUploadFromActivity();

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Upload from activity to check mediz sizes." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$videoName, $numOfMedia );

    $I->reloadPage();
    $I->wait( 3 );

    $I->assertGreaterThanOrEqual( ConstantsPage::$activityVideoPlayerWidth, $I->grabAttributeFrom( ConstantsPage::$videoSelectorActivity, 'style' ), "Width and height is as expected!" );
?>
