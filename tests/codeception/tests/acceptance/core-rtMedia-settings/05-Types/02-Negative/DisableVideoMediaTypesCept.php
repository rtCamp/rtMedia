<?php

/**
 * Scenario :Disable upload for video media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $allowed = false;
    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable upload for video media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$typesSettingsUrl );
    $verifyDisableStatusOfVideoMedieTypeCheckbox = $settings->verifyStatus( ConstantsPage::$videoLabel, ConstantsPage::$videoCheckbox );

    if ( $verifyDisableStatusOfVideoMedieTypeCheckbox ) {
        $settings->disableSetting( ConstantsPage::$videoCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing when Video Types are not allowed." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$videoName, $numOfMedia, $allowed );

    $I->reloadPage();

    $I->dontSeeElementInDOM( ConstantsPage::$firstVideoElementOnActivity );
    echo nl2br( "Video is not uploaded.. \n" );
?>
