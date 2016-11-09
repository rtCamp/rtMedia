<?php

/**
* Scenario : To check if media tab appears for group.
* Pre-requisite : In backend - Goto rtMedia settings -> Buddypress -> INTEGRATION WITH BUDDYPRESS FEATURES -> Enable media in group. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab appears for group');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $gotoGroup = new BuddypressSettingsPage($I);
    $gotoGroup->gotoGroupPage($I);

    $I->seeElement(BuddypressSettingsPage::$mediaLinkOnGroup);
?>
