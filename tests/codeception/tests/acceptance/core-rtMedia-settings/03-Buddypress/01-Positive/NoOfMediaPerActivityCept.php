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
	$loginPage->loginAsAdmin();

	$settings = new DashboardSettingsPage( $I );

	$settings->enableUploadFromActivity();

	$settings->setValue( ConstantsPage::$numOfMediaLabelActivity, ConstantsPage::$numOfMediaTextboxActivity, ConstantsPage::$numOfMediaPerPageOnActivity );

	$settings->disableDirectUpload();

	$settings->saveSettings();

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoActivity();

	$I->seeElementInDOM( ConstantsPage::$uploadButtonOnAtivityPage );

	$uploadmedia = new UploadMediaPage( $I );
	$uploadmedia->addStatus( "Bulk Upload from activity" );
	$uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, ConstantsPage::$numOfMediaPerPageOnActivity );

	$I->waitForElementNotVisible( ConstantsPage::$postUpdateButton, 10 );

	if ( ConstantsPage::$numOfMediaPerPageOnActivity > 0 ) {
		$I->waitForElementVisible( 'div.activity ul.activity-list li div.activity-content ul.rtmedia-list.rtm-activity-media-list.rtmedia-activity-media-length-4 li', 10 );
		$I->seeNumberOfElements( 'div.activity ul.activity-list li div.activity-content ul.rtmedia-list.rtm-activity-media-list.rtmedia-activity-media-length-4 li', ConstantsPage::$numOfMediaPerPageOnActivity );
	} else {
		$temp = 5;
		$I->seeNumberOfElements( ConstantsPage::$mediaPerPageActivitySelector, $temp );
	}
?>
