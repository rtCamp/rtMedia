<?php

/**
* Scenario : To check if Load More - Media display pagination option is enabled
* Pre-requisite : In backend - Goto rtMedia settings -> Display -> LIST MEDIA VIEW -> Media display pagination option -> Pagination option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Pagination - Media display pagination option is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $pagination = new UploadMediaPage($I);
    $pagination->gotoMediaPage($userName,$I);

    $I->wait(3);

    $I->seeElement(UploadMediaPage::$paginationPattern);
?>
