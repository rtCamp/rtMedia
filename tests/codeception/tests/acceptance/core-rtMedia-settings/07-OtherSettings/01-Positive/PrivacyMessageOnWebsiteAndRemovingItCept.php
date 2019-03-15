<?php

/**
 * Scenario : To check if the privacy Message is seen on website.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $scrollPos = ConstantsPage::$displayTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if the privacy Message is seen on website.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );
    $verifyEnableStatusOfPrivacyMessageCheckbox = $settings->verifyStatus( ConstantsPage::$showPrivacyMessageOnWebsiteLabel, ConstantsPage::$privacyMessageCheckbox, $scrollPos );

    if ( $verifyEnableStatusOfPrivacyMessageCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$privacyMessageCheckbox );
        $I->waitForElement( ConstantsPage::$privacyMessageTextarea, 5 );
        $I->fillField( ConstantsPage::$privacyMessageTextarea, ConstantsPage::$privacyMessageValue );
        $settings->saveSettings();
    }

    $I->amOnPage( '/' );
    $I->waitForElementVisible( ConstantsPage::$siteWidePrivacyNoticeSelector, 10 );
    $I->seeElement( ConstantsPage::$closePrivacyNoticeSelector );
    $I->click( ConstantsPage::$closePrivacyNoticeSelector );
    $I->reloadPage();
    $I->dontSee( ConstantsPage::$siteWidePrivacyNoticeSelector );

    /**
     * Below code is to check if the user clicks on close button in one tab then opening the site on other tab should not display privacy message.
     */
    $I->openNewTab();
    $I->amOnPage( '/' );
    $I->dontSee( ConstantsPage::$siteWidePrivacyNoticeSelector );

    /**
     * Below code is to check if the user clicks on close button in one window and oprning new window should display the privacy message.
     */

    $nick = $I->haveFriend('New Test Window');
    $nick->does(function(AcceptanceTester $I) {
         $I->amOnPage('/');
         $I->waitForElementVisible( ConstantsPage::$siteWidePrivacyNoticeSelector, 10 );
    });


?>
