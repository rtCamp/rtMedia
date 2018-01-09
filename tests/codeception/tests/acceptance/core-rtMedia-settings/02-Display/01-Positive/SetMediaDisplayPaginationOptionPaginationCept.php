<?php

/**
 * Scenario : To check if Pagination is enabled
 * Pre condition : The available no of Media should be  > ConstantsPage::$numOfMediaPerPage
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollPos = ConstantsPage::$numOfMediaTextbox;
	$scrollPosition = ConstantsPage::$customCssTab;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if Pagination is enabled' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$checkSelectionStatusOfPaginationRadioButton = $settings->verifyStatus( ConstantsPage::$strMediaDisplayPaginationLabel, ConstantsPage::$paginationRadioButton, $scrollPos );

	if ( $checkSelectionStatusOfPaginationRadioButton ) {
        echo nl2br( "Option is already selected." . "\n" );
    } else {
        $settings->selectOption( ConstantsPage::$paginationRadioButton );
        $settings->saveSettings();
    }

	$settings->setValue( ConstantsPage::$numOfMediaLabel, ConstantsPage::$numOfMediaTextbox, ConstantsPage::$numOfMediaPerPage, $scrollPosition );
	$settings->saveSettings();

	$settings->disableDirectUpload();

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	$uploadmedia = new UploadMediaPage( $I );

	if ( $totalMedia <= ConstantsPage::$numOfMediaPerPage ) {

		echo "inside if condition";

		$numOfMediaTobeUpload = ConstantsPage::$numOfMediaPerPage - $totalMedia + 1;
		echo "\n Media to be uploaded = " . $numOfMediaTobeUpload;

		$uploadmedia->uploadMedia( ConstantsPage::$imageName, $numOfMediaTobeUpload );
		$uploadmedia->uploadMediaUsingStartUploadButton();
	}
	$I->reloadPage();
	$I->seeElementInDOM( ConstantsPage::$paginationPattern );
?>
