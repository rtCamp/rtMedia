<?php

/**
 * Scenario : To set the number of media per page
 */
	use Page\Login as LoginPage;
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
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );

	if ( $I->grabValueFrom( ConstantsPage::$numOfMediaTextbox ) != ConstantsPage::$numOfMediaPerPage ) {

		$settings->setValue( ConstantsPage::$numOfMediaLabel, ConstantsPage::$numOfMediaTextbox, ConstantsPage::$numOfMediaPerPage, $scrollPos );
	}

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	$uploadmedia = new UploadMediaPage( $I );

	if ( $totalMedia == ConstantsPage::$numOfMediaPerPage ) {

		$I->seeNumberOfElements( ConstantsPage::$mediaPerPageOnMediaSelector, ConstantsPage::$numOfMediaPerPage );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$mediaTobeUploaded = ConstantsPage::$numOfMediaPerPage - $totalMedia;
		echo "\n Media to be uploaded = " . $mediaTobeUploaded;

		$uploadmedia->uploadMedia( ConstantsPage::$imageName, $mediaTobeUploaded );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();
		$I->seeNumberOfElements( ConstantsPage::$mediaPerPageOnMediaSelector, ConstantsPage::$numOfMediaPerPage );
	}
?>
