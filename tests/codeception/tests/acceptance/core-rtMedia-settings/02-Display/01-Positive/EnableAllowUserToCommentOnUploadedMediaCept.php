<?php

/**
 * Scenario : To Allow the user to comment on uploaded media.
 */
	use Page\Login as LoginPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\Constants as ConstantsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$commentStr = 'test comment';

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if the user is allowed to comment on uploaded media' );

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

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();
	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$buddypress->postComment( $commentStr );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();
		
		$uploadmedia = new UploadMediaPage( $I );
		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$buddypress->postComment( $commentStr );
	}

	$I->reloadPage();
?>
