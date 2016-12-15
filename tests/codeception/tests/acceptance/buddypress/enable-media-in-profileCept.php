<?php

/**
* Scenario : To check if media tab appears on profile
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab appears on profile');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->enableSetting($I,ConstantsPage::$strEnableMediaInProLabel,ConstantsPage::$enableMediaInProCheckbox);

    $url = 'members/'.ConstantsPage::$userName.'/profile';
    $I->amOnPage($url);

    $I->seeElement(ConstantsPage::$mediaLinkOnProfile);
?>
