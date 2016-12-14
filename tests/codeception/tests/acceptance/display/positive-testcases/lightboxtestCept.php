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
    $settings->enableSetting($I,ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, ConstantsPage::$lightboxScrollPosition);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton(ConstantsPage::$userName); //Assuming direct uplaod is disabled
    $uploadmedia->fisrtThumbnailMedia($I);

    $I->seeElement(ConstantsPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox
    $I->click(ConstantsPage::$closeButton);

?>
