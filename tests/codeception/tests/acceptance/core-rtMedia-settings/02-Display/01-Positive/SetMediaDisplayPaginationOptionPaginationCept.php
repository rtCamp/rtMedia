<?php

/**
 * Scenario : To check if Load More - Media display pagination option is enabled
 * Pre condition : The available no of Media should be  > ConstantsPage::$numOfMediaPerPage
 */

use Page\Login as LoginPage;
use Page\Constants as ConstantsPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;
use Page\UploadMedia as UploadMediaPage;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if Load More - Media display pagination option is enabled' );

$loginPage = new LoginPage($I);
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$displayTab,ConstantsPage::$displayTabUrl );
$settings->verifySelectOption( ConstantsPage::$strMediaDisplayPaginationLabel, ConstantsPage::$paginationRadioButton, ConstantsPage::$numOfMediaTextbox );
$settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox); //This will check if the direct upload is disabled

if( $I->grabValueFrom( ConstantsPage::$numOfMediaTextbox ) != ConstantsPage::$numOfMediaPerPage  ){

    $settings->setValue( ConstantsPage::$numOfMediaLabel, ConstantsPage::$numOfMediaTextbox, ConstantsPage::$numOfMediaPerPage, ConstantsPage::$customCssTab ); // 4th Arg refers the scrolling position
}

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$temp = $buddypress->countMedia(ConstantsPage::$mediaPerPageOnMediaSelector); // $temp will receive the available no. of media

$uploadmedia = new UploadMediaPage( $I );

if( $temp <= ConstantsPage::$numOfMediaPerPage ){

    echo "inside if condition";

    $numOfMediaTobeUpload = ConstantsPage::$numOfMediaPerPage - $temp + 1;
    echo "\n Media to b uploaded = ".$numOfMediaTobeUpload;

    for( $i = 0; $i < $numOfMediaTobeUpload ; $i++ ){

        $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName );

    }

}
$I->reloadPage();
$I->seeElementInDOM( ConstantsPage::$paginationPattern );
?>
