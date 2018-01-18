<?php

/**
 * Scenario : Disable create activity for media likes.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\UploadMedia as UploadMediaPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if activty is not created for media likes.' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

	$settings = new DashboardSettingsPage( $I );
	$settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
	$verifyEnableStatusOfMediaLikesCheckbox = $settings->verifyStatus( ConstantsPage::$mediaLikeCheckboxLabel, ConstantsPage::$mediaLikeCheckbox );

	if ( $verifyEnableStatusOfMediaLikesCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$mediaLikeCheckbox );
        $settings->saveSettings();
    }

    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );
    $verifyDisableStatusOfCreateActivityForMediaLikes = $settings->verifyStatus( ConstantsPage::$strCreateActivityMediaLikeLabel, ConstantsPage::$activityMediaLikeCheckbox );

    if ( $verifyDisableStatusOfCreateActivityForMediaLikes ) {
        $settings->disableSetting( ConstantsPage::$activityMediaLikeCheckbox );
		$settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoMedia();

	$totalMedia = $buddypress->countMedia( ConstantsPage::$mediaPerPageOnMediaSelector );

	if ( $totalMedia >= ConstantsPage::$minValue ) {

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$buddypress->firstThumbnailMedia();

		$I->seeElement( ConstantsPage::$likeButton );
		$I->executeJS( 'jQuery( ".rtmedia-item-comments .rtmedia-like" ).click();' );
		$I->waitForElement( ConstantsPage::$likeInfoSelector, 20 );
	} else {

		$settings->disableDirectUpload();

		$buddypress->gotoMedia();

        $uploadmedia = new UploadMediaPage( $I );
		$uploadmedia->uploadMedia( ConstantsPage::$imageName );
		$uploadmedia->uploadMediaUsingStartUploadButton();

		$I->reloadPage();

		$buddypress->firstThumbnailMedia();

		$I->seeElement( ConstantsPage::$likeButton );
		$I->executeJS( 'jQuery( ".rtmedia-item-comments .rtmedia-like" ).click();' );
		$I->waitForElement( ConstantsPage::$likeInfoSelector, 20 );
	}

    $buddypress->gotoActivity();
    $I->dontSeeElementInDOM( ConstantsPage::$activityMediaLikeSelector );
?>
