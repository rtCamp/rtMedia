<?php

/**
* Scenario : Disable upload from activity stream.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;


    $I = new AcceptanceTester($scenario);
    $I->wantTo('Disable upload from activity stream.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strMediaUploadFromActivityLabel,ConstantsPage::$mediaUploadFromActivityCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $I->dontSeeElementInDOM(ConstantsPage::$uploadButtonOnAtivityPage);

?>
