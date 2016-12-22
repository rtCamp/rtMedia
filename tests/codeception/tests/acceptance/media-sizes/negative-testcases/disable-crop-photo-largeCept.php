<?php

/**
* Scenario : To set photo large height and width when Crop is disaabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set photo large height and width when Crop is disaabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setMediaSize($I,ConstantsPage::$photoLargeLabel,ConstantsPage::$largeWidthTextbox,ConstantsPage::$LargeWidth,ConstantsPage::$largeHeightTextbox,ConstantsPage::$LargeHeight);

    $I->scrollTo(ConstantsPage::$topSaveButton);

    $settings->verifyDisableStatus($I,ConstantsPage::$photoLargeLabel,ConstantsPage::$largeCropChrckbox);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);(ConstantsPage::$userName);

    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'width');
    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'height');

?>
