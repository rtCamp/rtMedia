<?php

/**
* Scenario : To set the number media on Activity page while bulk upload.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $numOfMediaPerPage = '5';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set the number media on Activity page while bulk upload.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->setValue($I,ConstantsPage::$numOfMediaLabelActivity,ConstantsPage::$numOfMediaTextboxActivity,$numOfMediaPerPage);

    $temp = $I->grabValueFrom(ConstantsPage::$numOfMediaTextbox);
    $I->wait(3);

    if($temp == $numOfMediaPerPage){
        echo "Value matched!";
    }



?>
