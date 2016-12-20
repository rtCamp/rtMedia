<?php

/**
* Scenario : To set default privacy with public.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to set default privacy with public option');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$privacyTab,ConstantsPage::$privacyTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$privacyUserOverrideLabel,ConstantsPage::$privacyUserOverrideCheckbox);
    $settings->verifySelectOption($I,ConstantsPage::$defaultPrivacyLabel,ConstantsPage::$loggedInUsersRadioButton);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->addStatus($I);

    $I->seeElement(ConstantsPage::$privacyDropdown);

    if($I->grabValueFrom(ConstantsPage::$privacyDropdown) == '20'){
        echo nl2br("Default Privacy --> Loggedin Users \n");
    }else{
        echo nl2br("Test Failed \n");
    }

?>
