<?php

/**
* Scenario :To activate the plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'activate the plugin.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $I->seeElementInDOM( ConstantsPage::$plugin );
    $I->click( ConstantsPage::$plugin );
    $I->amOnPage( ConstantsPage::$gotoInactivePluginPage );

    $I->seeInSource( ConstantsPage::$refLabel );

    $setup = new SetupPage( $I );
    $setup->activatePlugin();

    $I->amOnPage( ConstantsPage::$gotoActivePluginPage );
    $I->seeElementInDOM( ConstantsPage::$deactivateLink );

    echo "Plugin is activated!";

?>
