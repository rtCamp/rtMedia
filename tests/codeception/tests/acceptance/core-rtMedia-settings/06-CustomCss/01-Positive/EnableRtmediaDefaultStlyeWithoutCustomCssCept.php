<?php

/**
* Scenario : Use default rtmedia style when custom code is not provided.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( ' Use default rtmedia style when custom code is not provided.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "value of textarea is = \n".$value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssEmptyValue );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
