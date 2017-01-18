<?php

/**
* Scenario : To set JPEG/JPG Image quality.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set JPEG/JPG Image quality.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setValue($I,ConstantsPage::$imgQualityLabel,ConstantsPage::$imgQualityTextbox,ConstantsPage::$imgQualityValue);

?>
