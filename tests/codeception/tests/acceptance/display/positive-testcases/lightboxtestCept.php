<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the lightbox is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox);

    $uploadmedia = new UploadMediaPage($I);

    $url = '/members'.ConstantsPage::$userName.'/media';
    $I->amOnPage($url);

    $tempArray = $I->grabMultiple('ul.rtm-gallery-list li');
    codecept_debug($tempArray);
    echo count($tempArray);

    if(count($tempArray) >= ConstantsPage::$minvalue){

        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
        $I->click(ConstantsPage::$closeButton);

    }else{

        $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);(ConstantsPage::$userName); //Assuming direct uplaod is disabled
        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
        $I->click(ConstantsPage::$closeButton);
    }

?>
