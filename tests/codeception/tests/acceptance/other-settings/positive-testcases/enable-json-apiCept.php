<?php

/**
* Scenario : Allow the user to enable Json API.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;


    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to enable Json API.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I, ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strEnableJsonDataLabel, ConstantsPage::$enableJsonDataCheckbox);

?>
