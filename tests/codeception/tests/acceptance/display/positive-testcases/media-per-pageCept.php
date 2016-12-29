<?php

/**
* Scenario : To set the number media per page
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To set the number media per page');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName, ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl);
    $settings->setValue($I,ConstantsPage::$numOfMediaLabel,ConstantsPage::$numOfMediaTextbox,ConstantsPage::$numOfMediaPerPage);

    $url = '/members'.ConstantsPage::$userName.'/media';
    $I->amOnPage($url);

    $tempArray = $I->grabMultiple('ul.rtm-gallery-list li');
    codecept_debug($tempArray);
    echo count($tempArray);

    if(count($tempArray) >= ConstantsPage::$numOfMediaPerPage){
        $I->seeNumberOfElements(ConstantsPage::$mediaPerPageOnMediaSelector,ConstantsPage::$numOfMediaPerPage);
    }else{
        $temp = ConstantsPage::$numOfMediaPerPage - count($tempArray);

        $uploadMedia = new UploadMediaPage($I);

        for($i = 0; $i < $temp; $i++ ){

            $uploadMedia->uploadMediaUsingStartUploadButton($I,ConstantsPage::$userName,ConstantsPage::$imageName,ConstantsPage::$photoLink);

        }

        $I->seeNumberOfElements(ConstantsPage::$mediaPerPageOnMediaSelector,ConstantsPage::$numOfMediaPerPage);
    }

?>
