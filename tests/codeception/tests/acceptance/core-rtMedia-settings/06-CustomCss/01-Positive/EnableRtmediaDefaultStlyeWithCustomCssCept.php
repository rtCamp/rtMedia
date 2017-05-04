<?php

/**
* Scenario : set custom css when default rtmedia style is enabled.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'set custom css style when default rtmedia style is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "Css text area value = \n".$value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $I->seeInSource( ConstantsPage::$customCssValue );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
