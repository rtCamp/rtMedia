<?php

/**
* Scenario : To set height and width of single video player.
*Prerequisite : Lightbox settings must be off.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set height and width of single video player.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl);
    $settings->setMediaSize($I,ConstantsPage::$singlePlayerLabel,ConstantsPage::$singleVideoWidthTextbox,ConstantsPage::$singleVideoWidth,ConstantsPage::$singleVideoHeightTextbox,ConstantsPage::$singleVideoHeight);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$videoName,ConstantsPage::$videoLink);
    $uploadmedia->fisrtThumbnailMedia($I);

    echo $I->grabAttributeFrom('div#rtm-mejs-video-container','style');

?>
