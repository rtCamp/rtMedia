<?php

/**
* Scenario : To set the number media on Activity page while bulk upload.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set the number media on Activity page while bulk upload.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strMediaUploadFromActivityLabel,ConstantsPage::$mediaUploadFromActivityCheckbox);

    $I->scrollTo(ConstantsPage::$topSaveButton);

    $settings->setValue($I,ConstantsPage::$numOfMediaLabelActivity,ConstantsPage::$numOfMediaTextboxActivity,ConstantsPage::$numOfMediaPerPageOnActivity);

    $buddypress = new BuddypressSettingsPage($I);
    $buddypress->gotoActivityPage($I,ConstantsPage::$userName);

    $I->seeElementInDOM(ConstantsPage::$uploadButtonOnAtivityPage);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->bulkUploadMediaFromActivity($I,ConstantsPage::$imageName,ConstantsPage::$numOfMediaPerPageOnActivity);    //Assuming Direct upload is disabled

    if(ConstantsPage::$numOfMediaPerPageOnActivity > 0){
            $I->seeNumberOfElements(ConstantsPage::$mediaPerPageActivitySelector,ConstantsPage::$numOfMediaPerPageOnActivity);
    }else{
        $temp = 5;
        $I->seeNumberOfElements(ConstantsPage::$mediaPerPageActivitySelector,$temp);
    }

?>
