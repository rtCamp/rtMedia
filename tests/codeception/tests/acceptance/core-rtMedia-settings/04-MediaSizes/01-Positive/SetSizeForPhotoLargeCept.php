<?php

/**
 * Scenario : To set photo large height.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo large height and width.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$photoLargeLabel, ConstantsPage::$largeWidthTextbox, ConstantsPage::$largeWidth, ConstantsPage::$largeHeightTextbox, ConstantsPage::$largeHeight );

    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$imageName );
    $uploadmedia->uploadMediaUsingStartUploadButton();

    $I->reloadPage();

    $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

    $buddypress->firstThumbnailMedia();

    $I->assertLessThanOrEqual( ConstantsPage::$largeWidth, $I->grabAttributeFrom( ConstantsPage::$largImgeSelector, 'width' ), "Width is as expected!" );
    $I->assertLessThanOrEqual( ConstantsPage::$largeHeight, $I->grabAttributeFrom( ConstantsPage::$largImgeSelector, 'height' ), "Height is as expected!" );

?>
