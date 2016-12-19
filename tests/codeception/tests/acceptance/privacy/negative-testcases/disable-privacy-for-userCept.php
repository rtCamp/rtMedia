<?php

/**
* Scenario : To disable the privacy settings for user.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To disable the privacy settings for user.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$privacyTab,ConstantsPage::$privacyTabUrl);
    $settings->disableSetting($I,ConstantsPage::$privacyUserOverrideLabel,ConstantsPage::$privacyUserOverrideCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->addStatus($I);

    $I->dontSeeElement(ConstantsPage::$privacyDropdown);

?>
