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

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "Css text area value = \n" . $value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );
    $settings->saveSettings();

    $I->reloadPage();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $bar = $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    return $webdriver->findElement(WebDriverBy::cssSelector('textarea#whats-new'))->getCSSValue('border-color');
    });

    $I->wait(5);

    $I->fillField('textarea#whats-new', 'HI..!I see this red color border!');
    $I->waitForElementVisible( ConstantsPage::$postUpdateButton, 60);

    $expected = 'rgb(255, 0, 0)';
    $actual = $bar;

    echo "Exepcted textarea border color = " . $expected . "\n";
    echo "Actual textarea border color = " . $actual . "\n";
    // $I->assertEquals( $bar, 'rgb(255, 0, 0)' );
    $I->assertEquals( $actual, $expected );

?>
