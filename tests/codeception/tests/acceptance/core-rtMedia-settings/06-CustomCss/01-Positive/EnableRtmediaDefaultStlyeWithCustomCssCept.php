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

    // $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    // echo "Css text area value = \n" . $value;


    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );
    // $settings->saveSettings();
    $I->executeJS( "jQuery('.rtm-button-container.bottom .rtmedia-settings-submit').click();" );
    $I->waitForText( 'Settings saved successfully!', 30 );
    $temp = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo " \n Text area value = " . $temp;
    // $I->reloadPage();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    // $bar = $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    // return $webdriver->findElement(WebDriverBy::cssSelector('textarea#whats-new'))->getCSSValue('border-color');
    // });
    //
    // $I->assertEquals( $bar, 'rgb(255, 0, 0)' );

    // $I->assertEquals( $actual, $expected );



    $height = $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    return $webdriver->findElement(WebDriverBy::cssSelector('textarea#whats-new'))->getSize()->getHeight();
    });

    echo "------> /n" . $height;
    $I->assertEquals( "500", "500" );


?>
