<?php

/**
* Scenario : To Allow the user to comment on uploaded media.
*/
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $commentStr = 'test comment';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to comment on uploaded media');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strCommentCheckboxLabel,ConstantsPage::$commentCheckbox);

    $url = '/members'.ConstantsPage::$userName.'/media';
    $I->amOnPage($url);

    $tempArray = $I->grabMultiple('ul.rtm-gallery-list li');
    codecept_debug($tempArray);
    echo count($tempArray);

    $uploadMedia = new UploadMediaPage($I);

    if(count($tempArray) >= ConstantsPage::$minvalue){

        $uploadMedia->fisrtThumbnailMedia($I);

        $I->seeElement(UploadMediaPage::$commentTextArea);
        $I->fillfield(UploadMediaPage::$commentTextArea,$commentStr);
        $I->click(UploadMediaPage::$commentSubmitButton);
        $I->wait(5);
        $I->see($commentStr);

        $I->reloadPage();
        $I->wait(5);

    }else{

        $uploadMedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);

        $I->reloadPage();
        $I->wait(7);

        $uploadMedia->fisrtThumbnailMedia($I);

        $I->seeElement(UploadMediaPage::$commentTextArea);
        $I->fillfield(UploadMediaPage::$commentTextArea,$commentStr);
        $I->click(UploadMediaPage::$commentSubmitButton);
        $I->wait(5);
        $I->see($commentStr);

        $I->reloadPage();
        $I->wait(5);

    }

?>
