<?php

/**
* Scenario :Allow upload for video media types.
*/
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Allow upload for video media types');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$typesTab,ConstantsPage::$typesTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$videoLabel,ConstantsPage::$videoCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaFromActivity($I,ConstantsPage::$videoName);

    $I->seeInSource('<li class="rtmedia-list-item media-type-video">');
    echo nl2br("Video is uploaded.. \n");

?>
