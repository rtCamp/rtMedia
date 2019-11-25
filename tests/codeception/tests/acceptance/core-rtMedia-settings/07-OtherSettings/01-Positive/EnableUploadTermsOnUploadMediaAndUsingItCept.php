<?php

/**
* Scenario : Enable Upload terms on Media Page and using it while uploading media.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $clickOnTermsOfServiceCheckbox = 'true';

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Enable Upload terms on Media Page and using it while uploading media.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin();

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$otherSettingsUrl );

    $verifyEnableStatusOfTermsOfServicesCheckboxOnMedia = $settings->verifyStatus( ConstantsPage::$mediaUploadTermsLabel, ConstantsPage::$mediaUploadTermsCheckbox, ConstantsPage::$scrollPosForOtherSettingsTab );

    if ( $verifyEnableStatusOfTermsOfServicesCheckboxOnMedia ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$mediaUploadTermsCheckbox );
        $settings->setValue( ConstantsPage::$termsOfServicePageLinkLabel, ConstantsPage::$termsOfServicePageLinkTextbox, ConstantsPage::$termsOfServicePageLinkValue );
        $settings->saveSettings();
    }

    $settings->assertTextboxNotEmpty();
    $settings->assertTextboxNotEmpty();
    $settings->disableDirectUpload();
    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$imageName );
    $uploadmedia->checkUploadTerms();
    $uploadmedia->uploadMediaUsingStartUploadButton();

?>
