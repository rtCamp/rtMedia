<?php

/**
* Scenario : To check if mesonry layout is disabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if mesonry layout is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox);

    $url = 'members/'.ConstantsPage::$userName.'/media/photo/';
    $I->amOnPage($url);

    $I->wait(5);

    $I->dontSeeElementInDOM(ConstantsPage::$masonryLayout);
?>
