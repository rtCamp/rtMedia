<?php

/**
 * Scenario : Should not allow the user to upload media directly.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if the user is allowed to upload the media directly' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $settings->disableDirectUpload();

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMedia( ConstantsPage::$imageName );
    $I->seeElement( ConstantsPage::$uploadMediaButton );

    $I->reloadPage();
?>
