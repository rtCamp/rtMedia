<?php

/**
* Scenario : To check if mesonry layout is enabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if mesonry layout is enabled.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox);

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoPhotoPage( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage($I);
    $temp = $uploadmedia->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    if($temp == 0){

        $I->amOnPage('/wp-admin');
        $I->wait(10);

        $settings->gotoTab( $I, ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
        $settings->verifyDisableStatus( $I, ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton( $I, ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

        $I->reloadPage();
        $I->wait(10);

        $I->seeElementInDOM( ConstantsPage::$masonryLayout );

    }else{

        $I->seeElementInDOM( ConstantsPage::$masonryLayout );

    }

?>
