<?php

/**
* Scenario : To set photo medium height and width when Crop is disabled.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo medium height and width when Crop is disabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$photoMediumLabel, ConstantsPage::$mediumWidthTextbox, ConstantsPage::$mediumWidth, ConstantsPage::$mediumHeightTextbox, ConstantsPage::$mediummHeight );

    $I->scrollTo( ConstantsPage::$topSaveButton );

    $settings->verifyDisableStatus( ConstantsPage::$photoThumbnailLabel, ConstantsPage::$mediumCropCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName );

    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector, 'width' );
    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector, 'height' );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
