<?php

/**
 * Scenario : To Check if the media is likable or not.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if the Like for media is disabled' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$verifyDisableStatusOfMediaLikesCheckbox = $settings->verifyStatus( ConstantsPage::$mediaLikeCheckboxLabel, ConstantsPage::$mediaLikeCheckbox );

    if ( $verifyDisableStatusOfMediaLikesCheckbox ) {
        $settings->disableSetting( ConstantsPage::$mediaLikeCheckbox );
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
		$I->dontSeeElement( ConstantsPage::$likeButton );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$buddypress->firstThumbnailMedia();
		$I->dontSeeElement( ConstantsPage::$likeButton );
	}
?>
