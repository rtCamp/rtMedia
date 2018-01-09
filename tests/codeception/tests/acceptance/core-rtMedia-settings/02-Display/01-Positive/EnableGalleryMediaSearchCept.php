<?php

/**
 * Scenario : To check if gallery media search is enable
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if gallery media search is enable' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $verifyEnableStatusOfGalleryMediaSearchCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableGalleryMediaSearchLabel, ConstantsPage::$mediaSearchCheckbox );

	if ( $verifyEnableStatusOfGalleryMediaSearchCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$mediaSearchCheckbox );
        $settings->saveSettings();
    }

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $I->seeElement( ConstantsPage::$mediaSearchSelector );
?>
