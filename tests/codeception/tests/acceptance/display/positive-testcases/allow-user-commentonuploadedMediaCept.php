<?php

/**
* Scenario : To Allow the user to comment on uploaded media.
*/
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $commentStr = 'test comment';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to comment on uploaded media');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strCommentCheckboxLabel,ConstantsPage::$commentCheckbox);

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoPhotoPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage($I);
    $temp = $uploadmedia->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    if($temp >= ConstantsPage::$minValue){

        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(UploadMediaPage::$commentTextArea);
        $I->fillfield(UploadMediaPage::$commentTextArea,$commentStr);
        $I->click(UploadMediaPage::$commentSubmitButton);
        $I->wait(5);
        $I->see($commentStr);

        $I->reloadPage();
        $I->wait(5);

    }else{

        $I->amOnPage('/wp-admin');
        $I->wait(10);

        $settings->gotoTab( $I, ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
        $settings->verifyDisableStatus( $I, ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);

        $I->reloadPage();
        $I->wait(7);

        $uploadmedia->fisrtThumbnailMedia($I);

        $I->seeElement(UploadMediaPage::$commentTextArea);
        $I->fillfield(UploadMediaPage::$commentTextArea,$commentStr);
        $I->click(UploadMediaPage::$commentSubmitButton);
        $I->wait(5);
        $I->see($commentStr);

        $I->reloadPage();
        $I->wait(5);

    }

?>
