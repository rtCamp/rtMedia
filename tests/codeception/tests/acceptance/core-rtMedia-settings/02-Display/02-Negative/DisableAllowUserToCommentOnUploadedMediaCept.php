<?php

/**
 * Scenario : Should not allow the user to comment on uploaded media.
 */
use Page\Login as LoginPage;
use Page\Constants as ConstantsPage;
use Page\UploadMedia as UploadMediaPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;

$scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'User should not allowed to comment on uploaded media' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );
    $uploadmedia = new UploadMediaPage( $I );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$temp = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

if ( $temp >= ConstantsPage::$minValue ) {

	$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $uploadmedia->firstThumbnailMedia();

	$I->waitForElementNotVisible( UploadMediaPage::$commentTextArea, 10 );
} else {
	$I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
	$I->waitForElement( ConstantsPage::$displayTab, 10 );
	$settings->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, $scrollToDirectUpload ); //This will check if the direct upload is disabled

	$buddypress->gotoMedia( ConstantsPage::$userName );

	$uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName );

	$I->reloadPage();
	// $I->waitForElement( ConstantsPage::$profilePicture, 5 );

	$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

	$uploadmedia->firstThumbnailMedia();

	$I->waitForElementNotVisible( UploadMediaPage::$commentTextArea, 10 );
}

    $I->reloadPage();

	?>
