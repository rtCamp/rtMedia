<?php

/**
* Scenario : To set photo medium height and width when Crop is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set photo medium height and width when Crop is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setMediaSize($I,ConstantsPage::$photoMediumLabel,ConstantsPage::$mediumWidthTextbox,ConstantsPage::$mediumWidth,ConstantsPage::$mediumHeightTextbox,ConstantsPage::$mediummHeight);

    $I->scrollTo(ConstantsPage::$topSaveButton);

    $settings->verifyEnableStatus($I,ConstantsPage::$photoThumbnailLabel,ConstantsPage::$mediumCropCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaFromActivity($I,ConstantsPage::$imageName);

    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'width');
    echo $I->grabAttributeFrom(ConstantsPage::$thumbnailSelector,'height');


?>
