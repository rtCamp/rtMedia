<?php

/**
* Scenario : To set photo large height and width when Crop is disaabled.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo large height and width when Crop is disaabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$photoLargeLabel, ConstantsPage::$largeWidthTextbox, ConstantsPage::$LargeWidth, ConstantsPage::$largeHeightTextbox, ConstantsPage::$LargeHeight );

    $I->scrollTo( ConstantsPage::$topSaveButton );

    $settings->verifyDisableStatus( ConstantsPage::$photoLargeLabel,ConstantsPage::$largeCropChrckbox);

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector,'width' );
    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector,'height');

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
