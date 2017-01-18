<?php

/**
* Scenario : To check if media tab is disabled for group.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab is disabled for group.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strEnableMediaInGrpLabel,ConstantsPage::$enableMediaInGrpCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoGroup($I);

    $I->dontSeeElement(ConstantsPage::$mediaLinkOnGroup);
?>
