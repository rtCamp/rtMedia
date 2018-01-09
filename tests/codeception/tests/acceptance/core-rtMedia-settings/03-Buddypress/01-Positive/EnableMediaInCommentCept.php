<?php

/**
 * Scenario : To Check if the user is allowed to upload media in comment.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$commentStr = 'This is the comment while uploading media.';
	$commentWithMedia = 'true';
	$scrollPos = ConstantsPage::$customCssTab;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( "Check if the user is allowed to upload media in comment" );

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

	$verifyEnableStatusOfMediaInCommentCheckbox = $settings->verifyStatus( ConstantsPage::$strMediaInCommnetLabel, ConstantsPage::$mediaInCommentCheckbox );
	if ( $verifyEnableStatusOfMediaInCommentCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$mediaInCommentCheckbox );
        $settings->saveSettings();
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	$uploadmedia = new UploadMediaPage( $I );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$buddypress->postComment( $commentStr, $commentWithMedia );

	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$buddypress->postComment( $commentStr, $commentWithMedia );
	}
?>
