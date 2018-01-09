<?php

/**
 * Scenario : To disable upload media in comment.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollPos = ConstantsPage::$customCssTab;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( "To disable upload media in comment." );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );

	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );

	$verifyEnableStatusOfAllowCommentCheckbox = $settings->verifyStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );
	if ( $verifyEnableStatusOfAllowCommentCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$commentCheckbox );
        $settings->saveSettings();
    }

	$verifyEnableStatusOfLightboxCheckbox = $settings->verifyStatus( ConstantsPage::$strLightboxCheckboxLabel, ConstantsPage::$lightboxCheckbox, $scrollPos );
	if ( $verifyEnableStatusOfLightboxCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$lightboxCheckbox );
        $settings->saveSettings();
    }

	$settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );

	$verifyEnableStatusOfMediaInProfileCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );
	if ( $verifyEnableStatusOfMediaInProfileCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$enableMediaInProCheckbox );
        $settings->saveSettings();
    }

	$verifyDisableStatusOfMediaInCommentCheckbox = $settings->verifyStatus( ConstantsPage::$strMediaInCommnetLabel, ConstantsPage::$mediaInCommentCheckbox );

	if ( $verifyDisableStatusOfMediaInCommentCheckbox ) {
		$settings->disableSetting( ConstantsPage::$mediaInCommentCheckbox );
		$settings->saveSettings();
	} else {
		echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	}

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();
	$totalCount = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	$uploadmedia = new UploadMediaPage( $I );

	if ( $totalCount >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$mediaButtonInComment );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$mediaButtonInComment );
	}
?>
