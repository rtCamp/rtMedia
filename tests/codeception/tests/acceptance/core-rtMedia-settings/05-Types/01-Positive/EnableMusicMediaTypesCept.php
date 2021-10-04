<?php

/**
 * Scenario :Allow upload for music media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use \Codeception\Step\Argument\PasswordArgument;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Allow upload for music media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, new PasswordArgument(ConstantsPage::$password) );

    $settings = new DashboardSettingsPage( $I );
    $settings->enableRequestedMediaTypes( ConstantsPage::$musicLabel, ConstantsPage::$musicCheckbox );

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing for Music Media Types." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$audioName, $numOfMedia );

    $I->waitForElementVisible( ConstantsPage::$firstMusicElementOnActivity, 20 );

    // $I->seeElementInDOM( ConstantsPage::$firstMusicElementOnActivity );
    echo nl2br( "Audio is uploaded.. \n" );
?>
