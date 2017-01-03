<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the lightbox is enabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

     $settings = new DashboardSettingsPage($I);
    // $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    // $settings->verifyEnableStatus($I,ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox);

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoPhotoPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage($I);
    $temp = $uploadmedia->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    if($temp >= ConstantsPage::$minvalue){

        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
        $I->click(ConstantsPage::$closeButton);

    }else{

        $I->amOnPage('/wp-admin');
        $I->wait(10);

        $settings->gotoTab( $I, ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
        $settings->verifyDisableStatus( $I, ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);(ConstantsPage::$userName); //Assuming direct uplaod is disabled

        $I->reloadPage();
        $I->wait(7);

        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
        $I->click(ConstantsPage::$closeButton);
    }

?>
