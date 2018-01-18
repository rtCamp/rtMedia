<?php

/**
 * Scenario : To set height and width of single video player.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of single video player.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$singlePlayerLabel, ConstantsPage::$singleVideoWidthTextbox, ConstantsPage::$singleVideoWidth, ConstantsPage::$singleVideoHeightTextbox, ConstantsPage::$singleVideoHeight );

    $settings->enableRequestedMediaTypes( ConstantsPage::$videoLabel, ConstantsPage::$videoCheckbox );

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$videoName );
    $uploadmedia->uploadMediaUsingStartUploadButton();

    $I->reloadPage();

    $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

    $buddypress->firstThumbnailMedia();

    echo $I->grabAttributeFrom( ConstantsPage::$videoSelectorSingle, 'style' );
    $I->assertGreaterThanOrEqual( ConstantsPage::$singleVideoWidth, $I->grabAttributeFrom( ConstantsPage::$videoSelectorSingle, 'style' ), "Width and height is as expected!" );
?>
