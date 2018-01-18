<?php

/**
 * Scenario : To set photo thumbnail height and width.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo thumbnail height and width.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$photoThumbnailLabel, ConstantsPage::$thumbnailWidthTextbox, ConstantsPage::$thumbnailWidth, ConstantsPage::$thumbnailHeightTextbox, ConstantsPage::$thumbnailHeight );

    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$imageName );
    $uploadmedia->uploadMediaUsingStartUploadButton();

    $I->reloadPage();

    $I->assertLessThanOrEqual( ConstantsPage::$thumbnailWidth, $I->grabAttributeFrom( ConstantsPage::$thumbnailImgSelector, 'width' ), "Width is as expected!" );
    $I->assertLessThanOrEqual( ConstantsPage::$thumbnailHeight, $I->grabAttributeFrom( ConstantsPage::$thumbnailImgSelector, 'height' ), "Height is as expected!" );
?>
