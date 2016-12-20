<?php

/**
* Scenario : To check if media tab is disabled on profile
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;


    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab is disabled on profile');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strEnableMediaInProLabel,ConstantsPage::$enableMediaInProCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoProfile($I,ConstantsPage::$userName);

    $I->dontSeeElement(ConstantsPage::$mediaLinkOnProfile);
?>
