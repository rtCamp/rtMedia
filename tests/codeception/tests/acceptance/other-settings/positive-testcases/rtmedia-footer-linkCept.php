<?php

/**
* Scenario : To check if rtMedia footer link is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;


    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check Add a link to rtMedia in footer is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I, ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl);
    $settings->enableSetting($I,ConstantsPage::$footerLinkLabel, ConstantsPage::$footerLinkCheckbox);

    $I->wait(5);

    $I->amOnPage('/');
    $I->seeElement(ConstantsPage::$footerLink);

?>
