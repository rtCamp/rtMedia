<?php

/**
* Scenario : To check if mesonry layout is enabled.
* Pre-requisite : In backend - Goto rtMedia settings -> MASONRY VIEW -> Enable Masonry Cascading grid layout. Assuming this option is on.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if mesonry layout is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $masonryLayout = new UploadMediaPage($I);
    $masonryLayout->gotoMediaPage($userName,$I);

    $I->wait(5);

    $I->seeElement(UploadMediaPage::$masonryLayout);

?>
