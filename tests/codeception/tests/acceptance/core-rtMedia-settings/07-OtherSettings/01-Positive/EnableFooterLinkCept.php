<?php

/**
 * Scenario : To check if rtMedia footer link is enabled.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollPos = ConstantsPage::$displayTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check Add a link to rtMedia in footer is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );
    $verifyEnableStatusOfFooterLinkCheckbox = $settings->verifyStatus( ConstantsPage::$footerLinkLabel, ConstantsPage::$footerLinkCheckbox, $scrollPos );

    if ( $verifyEnableStatusOfFooterLinkCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$footerLinkCheckbox );
        $settings->saveSettings();
    }

    $I->amOnPage( '/' );
    $I->waitForElementVisible( ConstantsPage::$footerLink, 10 );

?>
