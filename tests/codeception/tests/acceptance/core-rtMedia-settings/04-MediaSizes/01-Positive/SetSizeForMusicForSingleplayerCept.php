<?php

/**
 * Scenario : To set width of single music player.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set width of single music player.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$singlePlayerLabel, ConstantsPage::$singleMusicWidthTextbox, ConstantsPage::$singleMusicPlayerWidth );

    $settings->enableRequestedMediaTypes( ConstantsPage::$musicLabel, ConstantsPage::$musicCheckbox );

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$audioName );
    $uploadmedia->uploadMediaUsingStartUploadButton();

    $I->reloadPage();

    $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

    $buddypress->firstThumbnailMedia();

    $I->assertGreaterThanOrEqual( ConstantsPage::$singleMusicPlayerWidth, $I->grabAttributeFrom( ConstantsPage::$audioSelector, 'style' ), "Width and height is as expected!" );
?>
