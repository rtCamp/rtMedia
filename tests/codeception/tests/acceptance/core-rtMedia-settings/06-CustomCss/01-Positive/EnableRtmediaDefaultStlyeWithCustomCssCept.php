<?php

/**
* Scenario : set custom css when default rtmedia style is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'set custom css style when default rtmedia style is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "Css text area value = \n".$value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $I->seeInSource( ConstantsPage::$customCssValue );

?>
