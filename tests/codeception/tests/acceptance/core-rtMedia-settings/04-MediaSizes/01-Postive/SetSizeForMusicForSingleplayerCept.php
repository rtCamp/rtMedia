<?php

/**
* Scenario : To set width of single music player.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;
    $scrollPos = ConstantsPage::$customCssTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set width of single music player.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$singlePlayerLabel, ConstantsPage::$singleMusicWidthTextbox, ConstantsPage::$singleMusicPlayerWidth, $scrollPos );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$audioName );

    $I->reloadPage();
    // $I->waitForElement( ConstantsPage::$profilePicture, 5 );

    $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

    $uploadmedia->fisrtThumbnailMedia();

    echo $I->grabAttributeFrom( ConstantsPage::$audioSelectorSingle ,'style' );

    // $I->reloadPage();

?>
