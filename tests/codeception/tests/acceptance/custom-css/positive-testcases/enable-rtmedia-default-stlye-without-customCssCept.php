<?php

/**
* Scenario : Allow the user to set custom css for rtmedia.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to disable default style.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I, ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl);
    $settings->enableSetting($I,ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox);

    $value = $I->grabValueFrom(ConstantsPage::$cssTextaear);
    $settings->setValue($I,ConstantsPage::$customCssLabel,ConstantsPage::$cssTextaear,ConstantsPage::$customCssEmptyValue);

    echo "Custom css value = ";
    echo ConstantsPage::$customCssEmptyValue;
?>
