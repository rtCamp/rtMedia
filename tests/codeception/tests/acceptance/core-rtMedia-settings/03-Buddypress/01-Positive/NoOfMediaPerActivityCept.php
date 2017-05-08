<?php

/**
* Scenario : To set the number media on Activity page while bulk upload.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set the number media on Activity page while bulk upload.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox );

    $settings->setValue( ConstantsPage::$numOfMediaLabelActivity, ConstantsPage::$numOfMediaTextboxActivity, ConstantsPage::$numOfMediaPerPageOnActivity );

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
    $I->wait( 5 );
    $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $I->seeElementInDOM( ConstantsPage::$uploadButtonOnAtivityPage );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->bulkUploadMediaFromActivity( ConstantsPage::$imageName, ConstantsPage::$numOfMediaPerPageOnActivity );

    if( ConstantsPage::$numOfMediaPerPageOnActivity > 0 ){
            $I->seeNumberOfElements( ConstantsPage::$mediaPerPageActivitySelector, ConstantsPage::$numOfMediaPerPageOnActivity );
    }else{
        $temp = 5;
        $I->seeNumberOfElements( ConstantsPage::$mediaPerPageActivitySelector, $temp );
    }
?>
