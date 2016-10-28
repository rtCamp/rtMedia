<?php

/**
* Scenario : To check Admin bar menu integration is enabled.
* Pre-requisite : In backend - Goto rtMedia settings -> Other Seetings -> ADMIN SETTINGS -> Admin bar menu integration. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\OtherSettings as OtherSettingsPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Admin bar menu integration is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);     //This will require super user login credential.

    $I->wait(5);
    $I->seeElement(OtherSettingsPage::$rtMediaAdminbar);

?>
