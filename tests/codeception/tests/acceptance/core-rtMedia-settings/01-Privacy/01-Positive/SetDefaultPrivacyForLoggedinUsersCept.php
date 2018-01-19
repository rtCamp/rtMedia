<?php

/**
 * Scenario : To set default privacy for logged in user.
 */

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set default privacy for logged in user.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, ConstantsPage::$saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$privacySettingsUrl );

    $verifyEnableStatusOfPrivacyCheckbox = $settings->verifyStatus( ConstantsPage::$privacyLabel, ConstantsPage::$privacyCheckbox );

    if ( $verifyEnableStatusOfPrivacyCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$privacyCheckbox );
        $settings->saveSettings();
    }

    $verifySelectionStateOfPrivacyForLoggedInUserRadioButton = $settings->verifyStatus( ConstantsPage::$defaultPrivacyLabel, ConstantsPage::$loggedInUsersRadioButton );

	if ( $verifySelectionStateOfPrivacyForLoggedInUserRadioButton ) {
        echo nl2br( "Option is already selected." . "\n" );
    } else {
        $settings->selectOption( ConstantsPage::$loggedInUsersRadioButton );
        $settings->saveSettings();
    }

    $settings->enableUploadFromActivity();
    $settings->disableDirectUpload();
    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Upload from activity to test privacy for Logged-in users." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, $numOfMedia );

    $logout = new LogoutPage( $I );
    $logout->logout();

    $buddypress->gotoActivity();
    $I->dontSeeElementInDOM( ConstantsPage::$firstPhotoElementOnActivity );

?>
