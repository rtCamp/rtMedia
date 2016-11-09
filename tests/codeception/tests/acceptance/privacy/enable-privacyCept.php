<?php

/**
* Scenario : To check if privacy option is available
* Pre-requisite : In backend - Goto rtMedia settings -> Privacy -> PRIVACY SETTINGS -> Enable privacy. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\PrivacySettings as PrivacySettingsPage;
    use Page\UploadMedia as UploadMediaPage;


    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if privacy option is available.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $I->seeElement(UploadMediaPage::$whatIsNewTextarea);
    $I->click(UploadMediaPage::$whatIsNewTextarea);
    $I->wait(2);
    $I->seeElement(PrivacySettingsPage::$privacyDropdown);

?>
