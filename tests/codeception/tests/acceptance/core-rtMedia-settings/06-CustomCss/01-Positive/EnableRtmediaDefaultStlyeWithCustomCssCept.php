<?php

/**
 * Scenario : set custom css when default rtmedia style is enabled.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'set custom css style when default rtmedia style is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$customCssSettingsUrl );
    $verifyEnableStatusOfRtmediaDefaultStyleCheckbox = $settings->verifyStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    if ( $verifyEnableStatusOfRtmediaDefaultStyleCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$defaultStyleCheckbox );
        $settings->saveSettings();
    }

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "Css text area value = \n" . $value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );
    $settings->saveSettings();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $I->seeInSource( ConstantsPage::$customCssValue );
?>
