<?php

/**
 * Scenario : To Check if the media is opening in Light Box.
 */
use Page\Login as LoginPage;
use Page\UploadMedia as UploadMediaPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if the likes for media is enabled' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
$settings->verifyEnableStatus( ConstantsPage::$mediaLikeCheckboxLabel, ConstantsPage::$mediaLikeCheckbox ); //Last arg refers scroll postion

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$uploadmedia = new UploadMediaPage( $I );
$temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

if ( $temp >= ConstantsPage::$minValue ) {

	$I->scrollTo( '.rtm-gallery-title' );

	$uploadmedia->firstThumbnailMedia();

	$I->seeElement( ConstantsPage::$likeButton );
	$I->executeJS( 'jQuery( ".rtmedia-item-comments .rtmedia-like" ).click();' );
	$I->seeInSource( '<span>Unlike</span>' );
} else {

	//Disbale direct upload from settings
	$settings->disableDirectUpload();

	$buddypress->gotoMedia( ConstantsPage::$userName );
	$uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName );

	$I->reloadPage();

	$uploadmedia->firstThumbnailMedia();
	$I->seeElement( ConstantsPage::$likeButton );
	$I->executeJS( 'jQuery( ".rtmedia-item-comments .rtmedia-like" ).click();' );
	$I->seeInSource( '<span>Unlike</span>' );
}
?>
