<?php

/**
 * Scenario : To check Admin bar menu integration is enabled.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $scrollPos = ConstantsPage::$displayTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if Admin bar menu integration is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );
    $verifyEnableStatusOfAdminbarMenuCheckbox = $settings->verifyStatus( ConstantsPage::$adminbarMenuLabel, ConstantsPage::$adminbarMenuCheckbox, $scrollPos );

    if ( $verifyEnableStatusOfAdminbarMenuCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$adminbarMenuCheckbox );
        $settings->saveSettings();
    }

    $I->amOnPage( '/' );
    $I->waitForElementVisible( ConstantsPage::$rtMediaAdminbar, 10 );

?>
