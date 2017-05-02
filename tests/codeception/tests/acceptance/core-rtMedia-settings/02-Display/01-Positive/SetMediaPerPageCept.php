<?php

/**
* Scenario : To set the number media per page
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollPos = ConstantsPage::$customCssTab;
    $scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set the number media per page' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );

    if( $I->grabValueFrom( ConstantsPage::$numOfMediaTextbox ) != ConstantsPage::$numOfMediaPerPage  ){

        $settings->setValue( ConstantsPage::$numOfMediaLabel, ConstantsPage::$numOfMediaTextbox, ConstantsPage::$numOfMediaPerPage, $scrollPos );
    }

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );
    $temp = $buddypress->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

    $uploadmedia = new UploadMediaPage($I);

    if( $temp == ConstantsPage::$numOfMediaPerPage ){

        $I->seeNumberOfElements( ConstantsPage::$mediaPerPageOnMediaSelector, ConstantsPage::$numOfMediaPerPage );

    }else{

        $I->amOnPage('/wp-admin');
        $I->wait( 10 );

        $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
        $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, $scrollToDirectUpload); //This will check if the direct upload is disabled

        $buddypress->gotoMedia( ConstantsPage::$userName );

        $mediaTobeUploaded = ConstantsPage::$numOfMediaPerPage - $temp;

        for( $i = 0; $i < $mediaTobeUploaded; $i++ ){

            $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

        }

        $I->reloadPage();
        $I->wait( 10 );

        $I->seeNumberOfElements(ConstantsPage::$mediaPerPageOnMediaSelector,ConstantsPage::$numOfMediaPerPage);

    }

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
