<?php

/**
* Scenario : To check if Load More - Media display pagination option is enabled
* Pre-requisite : In backend - Goto rtMedia settings -> Display -> LIST MEDIA VIEW -> Media display pagination option -> Load More option must be selected.
*/

    use Page\Login as LoginPage;

    $userName = 'demo';
    $password = 'demo';
    $url = 'members/'.$userName.'/media'; //This wil take the user to media page


    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if Load More - Media display pagination option is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password); //It will call login function using Page object

    $I->amonPage($url);
    $I->wait(5);
    $I->see('Load More');

?>
