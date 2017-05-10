<?php

/**
* Scenario : To set width of Music player for activity page.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;
    $scrollPos = ConstantsPage::$customCssTab;
    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set height and width of video player for activity page' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$activityPlayerLabel, ConstantsPage::$activityMusicWidthTextbox, ConstantsPage::$activityMusicPlayerWidth, $scrollPos );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$audioName );

    echo $I->grabAttributeFrom( ConstantsPage::$audioSelectorActivity, 'style' );

?>
