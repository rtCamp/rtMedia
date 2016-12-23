<?php

/**
* Scenario : To set the number media per page
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set the number media per page');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->setValue($I,ConstantsPage::$numOfMediaLabel,ConstantsPage::$numOfMediaTextbox,ConstantsPage::$numOfMediaPerPage);

    $I->amOnPage('/members/rtcamp/media/');

    echo nl2br("No. of media per page = \n");
    $I->seeNumberOfElements(ConstantsPage::$mediaPerPageOnMediaSelector,ConstantsPage::$numOfMediaPerPage); //This will count the number of <li> tag.

?>
