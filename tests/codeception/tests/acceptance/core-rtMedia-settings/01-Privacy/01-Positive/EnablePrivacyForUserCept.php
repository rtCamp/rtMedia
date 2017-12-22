<?php

/**
 * Scenario : To enable privacy for users.
 */

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To enable privacy for user.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, ConstantsPage::$saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$privacySettingsUrl );

    $verifyEnableStatusOfPrivacyCheckbox = $settings->verifyStatus( ConstantsPage::$privacyLabel, ConstantsPage::$privacyCheckbox );

    if ( $verifyEnableStatusOfPrivacyCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$privacyCheckbox );
        $settings->saveSettings();
    }

    $verifyEnableStatusOfPrivacyUserOverrideCheckbox = $settings->verifyStatus( ConstantsPage::$privacyUserOverrideLabel, ConstantsPage::$privacyUserOverrideCheckbox );

    if ( $verifyEnableStatusOfPrivacyUserOverrideCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$privacyUserOverrideCheckbox );
        $settings->saveSettings();
    }

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $I->seeElementInDOM( ConstantsPage::$privacyDropdown );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
