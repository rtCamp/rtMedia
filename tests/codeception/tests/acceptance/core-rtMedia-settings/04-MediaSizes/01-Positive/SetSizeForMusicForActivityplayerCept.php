<?php

/**
 * Scenario : To set width of Music player for activity page.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of music player for activity page' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$activityPlayerLabel, ConstantsPage::$activityMusicWidthTextbox, ConstantsPage::$activityMusicPlayerWidth );

    $settings->enableRequestedMediaTypes( ConstantsPage::$musicLabel, ConstantsPage::$musicCheckbox );

    $settings->enableUploadFromActivity();

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Upload from activity to check mediz sizes." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$audioName, $numOfMedia );

    $I->reloadPage();
    $I->wait( 3 );

    $I->assertGreaterThanOrEqual( ConstantsPage::$activityMusicPlayerWidth, $I->grabAttributeFrom( ConstantsPage::$audioSelector, 'style' ), "Width and height is as expected!" );

?>
