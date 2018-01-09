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

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if masonry layout is enabled.' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$verifyEnableStatusOfMasonryCheckbox = $settings->verifyStatus( ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox, $scrollPosition );

	if ( $verifyEnableStatusOfMasonryCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$masonaryCheckbox );
        $settings->saveSettings();
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$uploadmedia = new UploadMediaPage( $I );
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia == 0 ) {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->seeElementInDOM( ConstantsPage::$masonryLayout );
	} else {
		$I->seeElementInDOM( ConstantsPage::$masonryLayout );
	}
?>
