<?php

/**
* Scenario : To check if mesonry layout is disabled.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if mesonry layout is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->disableSetting($I,ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox, ConstantsPage::$masonaryScrollPostion);

    $masonryLayout = new UploadMediaPage($I);
    $masonryLayout->gotoMediaPage(ConstantsPage::$userName,$I);

    $I->wait(5);

    $I->dontSeeElementInDOM(ConstantsPage::$masonryLayout);
?>
