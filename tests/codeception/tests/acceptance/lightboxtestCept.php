<?php

/**
* Scenario : To Check if the media is opening in Light Box.
* Pre-requisite : In backend - Goto rtMedia settings -> Display -> LIST MEDIA VIEW -> Use lightbox to display media. Assuming this option is on.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Lightbox as LightboxPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the lightbox is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password); //It will call login function using Page object

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMedia($userName); //It will upload media function using Page object

    $I->click(LightboxPage::$firstChild);    //This will click on the fisrt child element
    $I->wait(5);
    $I->seeElement(LightboxPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
    $I->click(LightboxPage::$closeButton);

?>
