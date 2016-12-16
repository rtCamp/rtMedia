<?php

/**
* Scenario : Allow user to Organize media into albums.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to Organize media into albums.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->disableSetting($I,ConstantsPage::$strEnableAlbumLabel,ConstantsPage::$enableAlbumCheckbox);


    $gotoMediaPage = new UploadMediaPage($I);
    $gotoMediaPage->gotoMediaPage(ConstantsPage::$userName,$I);

    $I->dontSeeElement(ConstantsPage::$mediaAlbumLink);

?>
