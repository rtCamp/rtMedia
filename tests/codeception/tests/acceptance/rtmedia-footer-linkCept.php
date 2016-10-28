<?php

/**
* Scenario : To check Add a link to rtMedia in footer is enabled.
* Pre-requisite : In backend - Goto rtMedia settings -> Other Seetings -> FOOTER LINK -> Add a link to rtMedia in footer. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\OtherSettings as OtherSettingsPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Admin bar menu integration is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $I->wait(5);
    $I->seeElement(OtherSettingsPage::$footerLink);

?>
