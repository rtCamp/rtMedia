<?php

/**
 * Scenario : To disable the privacy settings for user.
 */

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To disable the privacy settings for user.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$privacySettingsUrl );

    $verifyEnableStatusOfPrivacyCheckbox = $settings->verifyStatus( ConstantsPage::$privacyLabel, ConstantsPage::$privacyCheckbox );

    if ( $verifyEnableStatusOfPrivacyCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$privacyCheckbox );
        $settings->saveSettings();
    }

    $verifyDisableStatusOfPrivacyUserOverrideCheckbox = $settings->verifyStatus( ConstantsPage::$privacyUserOverrideLabel, ConstantsPage::$privacyUserOverrideCheckbox );

    if ( $verifyDisableStatusOfPrivacyUserOverrideCheckbox ) {
        $settings->disableSetting( ConstantsPage::$privacyUserOverrideCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $I->dontSeeElement( ConstantsPage::$privacyDropdown );

?>
