<?php

/**
 * Scenario :Disable upload for photo media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable upload for photo media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$typesSettingsUrl );
    $verifyDisableStatusOfPhotoMedieTypeCheckbox = $settings->verifyStatus( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    if ( $verifyDisableStatusOfPhotoMedieTypeCheckbox ) {
        $settings->disableSetting( ConstantsPage::$photoCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing when Photo Media Types are not allowed." );

    $uploadmedia->uploadMediaFromActivityWhenMediaFormatNotSupported( ConstantsPage::$imageName );

?>
