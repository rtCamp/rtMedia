<?php

/**
 * Scenario : To check if mesonry layout is disabled.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollPos = ConstantsPage::$numOfMediaTextbox;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if mesonry layout is enabled.' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );

	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $verifyDisableStatusOfMasonryCheckbox = $settings->verifyStatus( ConstantsPage::$strMasonaryCheckboxLabel, ConstantsPage::$masonaryCheckbox, $scrollPos );

    if ( $verifyDisableStatusOfMasonryCheckbox ) {
        $settings->disableSetting( ConstantsPage::$masonaryCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$uploadmedia = new UploadMediaPage( $I );
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector ); // $temp will receive the available no. of media

	if ( $totalMedia == 0 ) {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->dontSeeElementInDOM( ConstantsPage::$masonryLayout );
	} else {
		$I->dontSeeElementInDOM( ConstantsPage::$masonryLayout );
	}
?>
