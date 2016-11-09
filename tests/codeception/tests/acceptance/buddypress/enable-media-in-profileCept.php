<?php

/**
* Scenario : To check if media tab appears on profile
* Pre-requisite : In backend - Goto rtMedia settings -> Buddypress -> INTEGRATION WITH BUDDYPRESS FEATURES -> Enable media in profile. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab appears on profile');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $gotoProfile = new BuddypressSettingsPage($I);
    $gotoProfile->gotoProfilePage($userName,$I);

    $I->seeElement(BuddypressSettingsPage::$mediaLinkOnProfile);
?>
