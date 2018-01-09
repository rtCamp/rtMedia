<?php

/**
 * Scenario : To check if rtMedia footer link is disabled.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollPos = ConstantsPage::$displayTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if rtMedia footer link is disabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );
    $verifyDisableStatusOfFooterLinkCheckbox = $settings->verifyStatus( ConstantsPage::$footerLinkLabel, ConstantsPage::$footerLinkCheckbox, $scrollPos );

    if ( $verifyDisableStatusOfFooterLinkCheckbox ) {
        $settings->disableSetting( ConstantsPage::$footerLinkCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $I->amOnPage( '/' );
    $I->dontSeeElement( ConstantsPage::$footerLink );
?>
