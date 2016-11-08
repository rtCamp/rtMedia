<?php

/**
* Scenario : To check Admin bar menu integration is enabled.
* Pre-requisite : In backend - Goto rtMedia settings -> Other Seetings -> ADMIN SETTINGS -> Admin bar menu integration. This option must be selected.
* To run this test case we will need super user log in credentials.
*/

    use Page\Login as LoginPage;
    use Page\OtherSettings as OtherSettingsPage;

    $userName = 'admin';
    $password = 'rtdemo@18mar2016';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Admin bar menu integration is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $I->wait(5);
    $I->seeElement(OtherSettingsPage::$rtMediaAdminbar);

?>
