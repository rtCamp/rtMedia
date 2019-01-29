<?php

/**
 * Scenario : To check if Privacy Message is disabled.
 */
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollPos = ConstantsPage::$displayTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if Privacy Message is disabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );
    $verifyDisableStatusOfPrivacyMessageCheckbox = $settings->verifyStatus( ConstantsPage::$showPrivacyMessageOnWebsiteLabel, ConstantsPage::$privacyMessageCheckbox, $scrollPos );

    if ( $verifyDisableStatusOfPrivacyMessageCheckbox ) {
        $settings->disableSetting( ConstantsPage::$privacyMessageCheckbox );
        $I->waitForElementNotVisible( ConstantsPage::$privacyMessageTextarea, 5 );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $I->amOnPage( '/' );
    $I->dontSeeElement( ConstantsPage::$siteWidePrivacyNoticeSelector );
?>
