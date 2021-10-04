<?php

/**
 * Scenario : Disable Upload Terms of Services For Activity Page.
 */
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable Upload Terms of Services For Activity Page.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );

    $verifyDisableStatusOfTermsOfServicesCheckboxOnActivity = $settings->verifyStatus( ConstantsPage::$activityUploadTermsLabel, ConstantsPage::$activityUploadTermsCheckbox, ConstantsPage::$scrollPosForOtherSettingsTab );

    if ( $verifyDisableStatusOfTermsOfServicesCheckboxOnActivity ) {
        $settings->disableSetting( ConstantsPage::$activityUploadTermsCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }

    $settings->disableDirectUpload();
    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing when Upload Terms of Services add-on is disabled." );
    $I->dontSeeElement( ConstantsPage::$uploadTermsCheckbox );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, ConstantsPage::$numOfMedia );

?>
