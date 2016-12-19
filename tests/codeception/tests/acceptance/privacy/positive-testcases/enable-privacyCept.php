<?php

/**
* Scenario : To enable privacy in rtMedia.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to enable privacy for rtmedia');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$privacyTab,ConstantsPage::$privacyTabUrl);
    $settings->enableSetting($I,ConstantsPage::$privacyLabel,ConstantsPage::$privacyCheckbox);

?>
