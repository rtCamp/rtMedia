<?php

/**
* Scenario : To set height and width of single video player.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of single video player.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$singlePlayerLabel, ConstantsPage::$singleVideoWidthTextbox, ConstantsPage::$singleVideoWidth, ConstantsPage::$singleVideoHeightTextbox, ConstantsPage::$singleVideoHeight );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$videoName );

    $uploadmedia->fisrtThumbnailMedia();

    echo $I->grabAttributeFrom( ConstantsPage::$videoSelectorSingle, 'style' );

    // $I->reloadPage();
?>
