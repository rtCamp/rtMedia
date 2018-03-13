<?php

/**
 * Scenario :Allow upload for video media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Allow upload for video media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->enableRequestedMediaTypes( ConstantsPage::$videoLabel, ConstantsPage::$videoCheckbox );

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing for Video Media Types." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$videoName, $numOfMedia );

    $I->reloadPage();

    $I->seeElementInDOM( ConstantsPage::$firstVideoElementOnActivity );
    echo nl2br( "Video is uploaded.. \n" );
?>
