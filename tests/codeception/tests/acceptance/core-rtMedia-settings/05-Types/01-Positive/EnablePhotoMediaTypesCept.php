<?php

/**
 * Scenario :Allow upload for photo media types.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use \Codeception\Step\Argument\PasswordArgument;

    $numOfMedia = 1;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Allow upload for photo media types' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, new PasswordArgument(ConstantsPage::$password) );

    $settings = new DashboardSettingsPage( $I );
    $settings->enableRequestedMediaTypes( ConstantsPage::$photoLabel, ConstantsPage::$photoCheckbox );

    $settings->enableUploadFromActivity();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->addStatus( "Testing for Photo Media Types." );
    $uploadmedia->uploadMediaFromActivity( ConstantsPage::$imageName, $numOfMedia );

    $I->waitForElementVisible( ConstantsPage::$firstPhotoElementOnActivity, 20 );

    $I->seeElementInDOM( ConstantsPage::$firstPhotoElementOnActivity );
    echo nl2br( "Photo is uploaded.. \n" );
?>
