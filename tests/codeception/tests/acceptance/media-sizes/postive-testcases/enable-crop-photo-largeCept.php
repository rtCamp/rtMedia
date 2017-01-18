<?php

/**
* Scenario : To set photo large height and width when Crop is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set photo large height and width when Crop is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setMediaSize($I,ConstantsPage::$photoLargeLabel,ConstantsPage::$largeWidthTextbox,ConstantsPage::$LargeWidth,ConstantsPage::$largeHeightTextbox,ConstantsPage::$LargeHeight);

    $I->scrollTo(ConstantsPage::$topSaveButton);

    $settings->verifyEnableStatus($I,ConstantsPage::$photoLargeLabel,ConstantsPage::$largeCropChrckbox);

    $I->scrollTo(ConstantsPage::$topSaveButton);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$strLightboxCheckboxLabel,ConstantsPage::$lightboxCheckbox);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);(ConstantsPage::$userName);
    $uploadmedia->fisrtThumbnailMedia($I);

    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'width');
    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'height');

?>
