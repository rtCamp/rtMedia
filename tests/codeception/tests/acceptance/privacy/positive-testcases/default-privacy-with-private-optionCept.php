<?php

/**
* Scenario : To set default privacy with public.
* Prerequisites : This option must be on -> 'Allow users to set privacy for their content'
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
    $settings->selectOption($I,ConstantsPage::$defaultPrivacyLabel,ConstantsPage::$privateRadioButton);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->addStatus($I);

    $I->seeElement(ConstantsPage::$privacyDropdown);

    $temp = $I->grabValueFrom(ConstantsPage::$privacyDropdown);
    echo "Value from select box = ";
    echo $temp;

    if($temp == '60'){
        echo "Test Passed";
    }else{
        echo "Test Failed";
    }

?>
