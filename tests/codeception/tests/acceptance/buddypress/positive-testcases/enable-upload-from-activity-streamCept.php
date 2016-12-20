<?php

/**
* Scenario : Allow upload from activity stream.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;


    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to upload media from activity stream.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strMediaUploadFromActivityLabel,ConstantsPage::$mediaUploadFromActivityCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $I->seeElementInDOM(ConstantsPage::$uploadButtonOnAtivityPage);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaFromActivity($I);  //Assuming Direct upload is not enabled

?>
