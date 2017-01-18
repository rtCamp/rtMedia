<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the lightbox is disabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink); //Assuming direct uplaod is disabled
    $uploadmedia->fisrtThumbnailMedia($I);

    $I->dontSeeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox

?>
