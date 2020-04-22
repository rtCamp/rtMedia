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
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$customCssSettingsUrl );
    $verifyEnableStatusOfRtmediaDefaultStyleCheckbox = $settings->verifyStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    if ( $verifyEnableStatusOfRtmediaDefaultStyleCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$defaultStyleCheckbox );
        $settings->saveSettings();
    }


    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );
    // $settings->saveSettings();
    $I->executeJS( "jQuery('.rtm-button-container.bottom .rtmedia-settings-submit').click();" );
    $I->waitForText( 'Settings saved successfully!', 30 );
    $temp = $I->grabTextFrom( ConstantsPage::$cssTextarea );
    echo " \n Text area value = " . $temp;


    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $optionDivColor = $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    return $webdriver->findElement(WebDriverBy::cssSelector('.rtm-media-options '))->getCSSValue('color');
    });

    echo "\n Option div button color = ". $optionDivColor;
    echo "\n";
    $I->assertEquals( $optionDivColor, 'rgba(34, 139, 34, 1)' );

?>
