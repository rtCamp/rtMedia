<?php

/**
* Scenario : Allow user to disable media notification.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to disable media notification.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->disableSetting($I,ConstantsPage::$strMediaNotificationLabel,ConstantsPage::$mediaNotificationCheckbox);

?>
