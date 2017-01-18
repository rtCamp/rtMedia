<?php

/**
* Scenario : Should not allow the user to comment on uploaded media.
*/
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('User should not allowed to comment on uploaded media');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strCommentCheckboxLabel,ConstantsPage::$commentCheckbox);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);

    $I->reloadPage();
    $I->wait(7);

    $uploadmedia->fisrtThumbnailMedia($I);

    $I->dontSeeElement(UploadMediaPage::$commentTextArea);

    $I->reloadPage();
    $I->wait(5);

?>
