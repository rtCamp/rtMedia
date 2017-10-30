<?php

/**
 * Scenario :Disable upload for music media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $allowed = false;
    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable upload for music media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

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
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$audioName, $numOfMedia, $allowed );

    $I->reloadPage();

    $I->dontSeeElementInDOM( ConstantsPage::$firstMusicElementOnActivity );
    echo nl2br( "Audio is not uploaded.. \n" );
?>
