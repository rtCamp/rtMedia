<?php

/**
* Scenario : Allow user to Organize media into albums.
* Pre-requisite : In backend - Goto rtMedia settings -> Buddypress -> ALBUM SETTINGS -> ALBUM SETTINGS. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to Organize media into albums.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $gotoMediaPage = new UploadMediaPage($I);
    $gotoMediaPage->gotoMediaPage($userName,$I);

    $I->seeElement(BuddypressSettingsPage::$mediaAlbumLink);

?>
