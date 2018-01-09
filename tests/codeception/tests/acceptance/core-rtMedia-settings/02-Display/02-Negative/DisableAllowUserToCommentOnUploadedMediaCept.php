<?php

/**
 * Scenario : Should not allow the user to comment on uploaded media.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$scrollToDirectUpload = ConstantsPage::$masonaryCheckbox;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'User should not allowed to comment on uploaded media' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$verifyDisableStatusOfAllowCommentCheckbox = $settings->verifyStatus( ConstantsPage::$strCommentCheckboxLabel, ConstantsPage::$commentCheckbox );

    if ( $verifyDisableStatusOfAllowCommentCheckbox ) {
        $settings->disableSetting( ConstantsPage::$commentCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

	$uploadmedia = new UploadMediaPage( $I );

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$commentTextArea );

	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();
		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$commentTextArea );
	}

	$I->reloadPage();
?>
