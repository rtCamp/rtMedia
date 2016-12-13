<?php

/**
* Scenario : To check if Load More - Media display pagination option is enabled
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'krupa';
    $password = 'Test123';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Load More - Media display pagination option is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->checkLoadmoreOption($I);

    $loadMore = new UploadMediaPage($I);
    $loadMore->gotoMediaPage($userName,$I);

    $I->wait(3);

    $I->seeElementInDOM(UploadMediaPage::$loadMore);

?>
