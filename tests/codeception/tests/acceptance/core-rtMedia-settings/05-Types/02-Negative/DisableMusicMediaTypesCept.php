<?php

/**
 * Scenario :Disable upload for music media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    // $isMediaSupported = false;
    // $allowed = true;
    // $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable upload for music media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$typesSettingsUrl );
    $verifyDisableStatusOfMusicMedieTypeCheckbox = $settings->verifyStatus( ConstantsPage::$musicLabel, ConstantsPage::$musicCheckbox );

    if ( $verifyDisableStatusOfMusicMedieTypeCheckbox ) {
        $settings->disableSetting( ConstantsPage::$musicCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing when Music Media Types are not allowed." );

    $uploadmedia->uploadMediaFromActivityWhenMediaFormatNotSupported( ConstantsPage::$audioName );

?>
