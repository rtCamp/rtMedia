<?php

/**
* Scenario : To Allow the user to comment on uploaded media.
* Pre-requisite : In backend - Goto rtMedia settings -> Display -> SINGLE MEDIA VIEW -> Allow user to comment on uploaded media. Assuming this option is on.
*/
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allwed to comment on uploaded media');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password); //It will call login function using Page object

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMedia($userName); //It will upload media function using Page object

    $I->click(UploadMediaPage::$firstChild); //This will click on the fisrt child element
    $I->wait(5);
    $I->seeElement(UploadMediaPage::$commentTextArea);
    $I->fillfield(UploadMediaPage::$commentTextArea,"test"); //Add you comment here
    $I->click(UploadMediaPage::$commentSubmitButton);
    $I->wait(5);
    $I->see('test');

    $I->reloadPage();
    $I->wait(5);

?>
