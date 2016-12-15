<?php

/**
* Scenario : To set the number media per page
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $numOfMediaPerPage = '2';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set the number media per page');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->setValue($I,ConstantsPage::$numOfMediaLabel,ConstantsPage::$numOfMediaTextbox,$numOfMediaPerPage);

    $temp = $I->grabValueFrom(ConstantsPage::$numOfMediaTextbox);

    if($temp == $numOfMediaPerPage){
        echo "Value matched!";
    }



?>
