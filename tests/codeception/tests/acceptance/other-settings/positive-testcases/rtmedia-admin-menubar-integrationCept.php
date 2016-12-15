<?php

/**
* Scenario : To check Admin bar menu integration is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Admin bar menu integration is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$otherSeetingsTab,ConstantsPage::$otherSeetingsTabUrl);
    $settings->enableSetting($I,ConstantsPage::$adminbarMenuLabel, ConstantsPage::$adminbarMenuCheckbox);

    $I->seeElement(ConstantsPage::$rtMediaAdminbar);

?>
