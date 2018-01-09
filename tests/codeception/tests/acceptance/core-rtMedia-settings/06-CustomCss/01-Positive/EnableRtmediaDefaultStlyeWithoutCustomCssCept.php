<?php

/**
 * Scenario : Use default rtmedia style when custom code is not provided.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( ' Use default rtMedia style when custom code is not provided.' );

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
    echo "value of textarea is = \n" . $value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssEmptyValue );
    $settings->saveSettings();
    
    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $I->dontSeeInSource( ConstantsPage::$customCssValue );
?>
