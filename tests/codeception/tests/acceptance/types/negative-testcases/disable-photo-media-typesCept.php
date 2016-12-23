<?php

/**
* Scenario :Disable upload for photo media types.
*/
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $commentStr = 'test comment';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Disable upload for photo media types');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$typesTab,ConstantsPage::$typesTabUrl);
    $settings->verifyDisableStatus($I,ConstantsPage::$photoLabel,ConstantsPage::$photoCheckbox);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaFromActivity($I,ConstantsPage::$imageName);

    $I->dontSeeInSource('<li class="rtmedia-list-item media-type-photo"></li>');
    echo nl2br("Photo is not uploaded.. \n");


?>
