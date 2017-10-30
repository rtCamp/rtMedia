<?php

/**
 * Scenario : To Check if the media is opening in Light Box.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollPos = ConstantsPage::$customCssTab;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if the lightbox is disabled' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $verifyDisableStatusOfLightboxCheckbox = $settings->verifyStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, $scrollPos );

    if ( $verifyDisableStatusOfLightboxCheckbox ) {
        $settings->disableSetting( ConstantsPage::$lightboxCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$uploadmedia = new UploadMediaPage( $I );
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$closeButton );   //The close button will only be visible if the media is opened in Lightbox
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();
		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();
		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$closeButton );
	}
?>
