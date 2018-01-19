<?php

/**
 * Scenario : Enable create activity for media comments.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $commentStr = 'test comment for created activity';

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Check if the activity is created for media comments.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );

    $verifyEnableStatusOfCreateActivityForMediaComments = $settings->verifyStatus( ConstantsPage::$strCreateActivityForMediaCommentLabel, ConstantsPage::$activityMediaCommentCheckbox );
	if ( $verifyEnableStatusOfCreateActivityForMediaComments ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$activityMediaCommentCheckbox );
        $settings->saveSettings();
    }

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

    $buddypress->gotoActivity();
    $I->seeElementInDOM( ConstantsPage::$activityMediaCommentSelector );

?>
