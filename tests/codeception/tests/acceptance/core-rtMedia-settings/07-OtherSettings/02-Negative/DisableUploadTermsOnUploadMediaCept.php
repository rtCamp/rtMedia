<?php

/**
 * Scenario : Disable Upload Terms of Services For Media Page.
 */
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable Upload Terms of Services For Media Page.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );

    $verifyDisableStatusOfTermsOfServicesCheckboxOnMedia = $settings->verifyStatus( ConstantsPage::$mediaUploadTermsLabel, ConstantsPage::$mediaUploadTermsCheckbox, ConstantsPage::$scrollPosForOtherSettingsTab );

    if ( $verifyDisableStatusOfTermsOfServicesCheckboxOnMedia ) {
        $settings->disableSetting( ConstantsPage::$mediaUploadTermsCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $settings->disableDirectUpload();
    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$imageName );
    $I->dontSeeElement( ConstantsPage::$uploadTermsCheckbox );
    $uploadmedia->uploadMediaUsingStartUploadButton();

?>
