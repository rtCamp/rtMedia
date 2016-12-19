<?php

/**
* Scenario : To check if Load More - Media display pagination option is enabled
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Load More - Media display pagination option is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->selectOption($I,ConstantsPage::$strMediaDisplayPaginationLabel,ConstantsPage::$loadmoreRadioButton);

    $url = 'members/'.ConstantsPage::$userName.'/media/photo/';
    $I->amOnPage($url);

    $I->wait(3);

    $I->seeElementInDOM(ConstantsPage::$loadMore);

?>
