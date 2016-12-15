<?php

/**
* Scenario : To check Admin bar menu integration is disabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Admin bar menu integration is disabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$otherSeetingsTab,ConstantsPage::$otherSeetingsTabUrl);
    $settings->disableSetting($I,ConstantsPage::$adminbarMenuLabel, ConstantsPage::$adminbarMenuCheckbox);

    $I->dontSeeElement(ConstantsPage::$rtMediaAdminbar);

?>
