<?php

/**
* Scenario :To upload plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Upload plugin.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $I->seeElementInDOM( ConstantsPage::$plugin );
    $I->click( ConstantsPage::$plugin );
    $I->wait( 5 );
    $I->seeInCurrentUrl( ConstantsPage::$gotoPluginPage );
    $I->dontSeeInSource( ConstantsPage::$refLabel );

    $setup = new SetupPage( $I );
    $setup->uploadAndInstallPlugin();

    $I->amOnPage( ConstantsPage::$gotoInactivePluginPage );
    $I->wait( 5 );

    $I->seeInSource( ConstantsPage::$refLabel );

    echo "Plugin is uploaded!"

?>
