<?php

/**
* Scenario : To check if mesonry layout is disabled.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'krupa';
    $password = 'Test123';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if mesonry layout is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->disableMasonayLayout($I);

    $masonryLayout = new UploadMediaPage($I);
    $masonryLayout->gotoMediaPage($userName,$I);

    $I->wait(5);

    $I->dontSeeElementInDOM(UploadMediaPage::$masonryLayout);
?>
