<?php

/**
* Scenario : Use default rtmedia style when custom code is not provided.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( ' Use default rtmedia style when custom code is not provided.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );

    $value = $I->grabValueFrom( ConstantsPage::$cssTextarea );
    echo "value of textarea is = \n".$value;
    $settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssEmptyValue );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $I->dontSeeInSource( ConstantsPage::$customCssValue );

?>
