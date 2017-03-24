<?php

/**
* Scenario : To set height and width of single video player.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of single video player.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$singlePlayerLabel, ConstantsPage::$singleVideoWidthTextbox, ConstantsPage::$singleVideoWidth, ConstantsPage::$singleVideoHeightTextbox, ConstantsPage::$singleVideoHeight );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
    $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, $scrollToDirectUpload );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$videoName, ConstantsPage::$videoLink );
    $uploadmedia->fisrtThumbnailMedia();

    echo $I->grabAttributeFrom( ConstantsPage::$videoSelectorSingle, 'style' );

?>
