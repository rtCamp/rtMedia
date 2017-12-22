<?php

/**
 * Scenario : To Check if the media is opening in Light Box.
 */
	use Page\Login as LoginPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\Constants as ConstantsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollPos = ConstantsPage::$customCssTab;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if the lightbox is enabled' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$verifyEnableStatusOfLightboxCheckbox = $settings->verifyStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, $scrollPos );

	if ( $verifyEnableStatusOfLightboxCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$lightboxCheckbox );
        $settings->saveSettings();
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$uploadmedia = new UploadMediaPage( $I );
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();

		$I->seeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox
		$I->click( ConstantsPage::$closeButton );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();
		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();
		$buddypress->firstThumbnailMedia();

		$I->seeElement( ConstantsPage::$closeButton );
		$I->click( ConstantsPage::$closeButton );
	}
?>
