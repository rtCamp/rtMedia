<?php

/**
* Scenario : To check if mesonry layout is disabled.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if mesonry layout is enabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox, ConstantsPage::$numOfMediaTextbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $uploadmedia = new UploadMediaPage( $I );
    $temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

    if($temp == 0){

        $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
        $I->waitForElement( ConstantsPage::$displayTab , 10);
        $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox ); //This will check if the direct upload is disabled

        $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink );

        $I->reloadPage();
        $I->wait( 10 );

        $I->dontSeeElementInDOM(ConstantsPage::$masonryLayout);

    }else{

        $I->dontSeeElementInDOM(ConstantsPage::$masonryLayout);

    }

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
