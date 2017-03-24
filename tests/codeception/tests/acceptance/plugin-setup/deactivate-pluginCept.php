<?php

/**
* Scenario :To deactivate the plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Deactivate the plugin.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $I->seeElementInDOM( ConstantsPage::$plugin );
    $I->click( ConstantsPage::$plugin );
    $I->amOnPage( ConstantsPage::$gotoActivePluginPage );

    $I->seeInSource( ConstantsPage::$refLabel );

    $setup = new SetupPage( $I );
    $setup->deactivatePlugin();

    $I->amOnPage( ConstantsPage::$gotoInactivePluginPage );
    $I->seeElementInDOM( ConstantsPage::$activateLink );

    echo "Plugin is deactivated!";

?>
