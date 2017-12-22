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

	$settings->enableUploadFromActivity();

	$settings->setValue( ConstantsPage::$numOfMediaLabelActivity, ConstantsPage::$numOfMediaTextboxActivity, ConstantsPage::$numOfMediaPerPageOnActivity );

	$settings->disableDirectUpload();

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoActivity();

	$I->seeElementInDOM( ConstantsPage::$uploadButtonOnAtivityPage );

	$uploadmedia = new UploadMediaPage( $I );
	$uploadmedia->addStatus( "Bulk Upload from activity" );
	$uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, ConstantsPage::$numOfMediaPerPageOnActivity );

	$I->reloadPage();

	if ( ConstantsPage::$numOfMediaPerPageOnActivity > 0 ) {
		$I->waitForElementVisible( ConstantsPage::$mediaPerPageActivitySelector, 10 );
		$I->seeNumberOfElements( ConstantsPage::$mediaPerPageActivitySelector, ConstantsPage::$numOfMediaPerPageOnActivity );
	} else {
		$temp = 5;
		$I->seeNumberOfElements( ConstantsPage::$mediaPerPageActivitySelector, $temp );
	}
?>
