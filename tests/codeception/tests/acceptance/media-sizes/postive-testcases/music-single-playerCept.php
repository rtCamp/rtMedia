<?php

/**
* Scenario : To set width of single music player.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo(' To set width of single music player.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setMediaSize($I,ConstantsPage::$singlePlayerLabel,ConstantsPage::$singleMusicWidthTextbox,ConstantsPage::$singleMusicPlayerWidth);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$audioName,ConstantsPage::$musicLink);
    $uploadmedia->fisrtThumbnailMedia($I);

    echo $I->grabAttributeFrom(ConstantsPage::$audioSelectorSingle,'style');

?>
