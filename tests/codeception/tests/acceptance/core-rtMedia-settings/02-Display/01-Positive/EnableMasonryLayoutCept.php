<?php

/**
 * Scenario : To check if masonry layout is enabled.
 */

use Page\Login as LoginPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;
use Page\UploadMedia as UploadMediaPage;

$scrollPosition = ConstantsPage::$numOfMediaTextbox;
$scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if masonry layout is enabled.' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
$settings->verifyEnableStatus( ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox, $scrollPosition );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$uploadmedia = new UploadMediaPage( $I );
$temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

if($temp == 0){

    $I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
    $I->waitForElement( ConstantsPage::$displayTab , 10);
    $settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, $scrollToDirectUpload ); //This will check if the direct upload is disabled

    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName );

    $I->reloadPage();
    $I->seeElementInDOM( ConstantsPage::$masonryLayout );

}else{

    $I->seeElementInDOM( ConstantsPage::$masonryLayout );

}

?>
