<?php

/**
 * Scenario : To set photo medium height and width.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo medium height and width.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$mediaSizeSettingsUrl );
    $settings->setMediaSize( ConstantsPage::$photoMediumLabel, ConstantsPage::$mediumWidthTextbox, ConstantsPage::$mediumWidth, ConstantsPage::$mediumHeightTextbox, ConstantsPage::$mediumHeight );

    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $settings->enableUploadFromActivity();

    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Upload from activity to check mediz sizes." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, $numOfMedia );

    $I->reloadPage();

    $I->assertLessThanOrEqual( ConstantsPage::$mediumWidth, $I->grabAttributeFrom( ConstantsPage::$mediumImgSelector, 'width' ), "Width is as expected!" );
    $I->assertLessThanOrEqual( ConstantsPage::$mediumHeight, $I->grabAttributeFrom( ConstantsPage::$mediumImgSelector, 'height' ), "Height is as expected!" );

?>
