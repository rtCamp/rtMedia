<?php

/**
* Scenario : To check direct media upload.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to upload the media directly');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->enableSetting($I,ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaDirectly(ConstantsPage::$userName);

?>
