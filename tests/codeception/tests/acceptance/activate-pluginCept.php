<?php

/**
* Scenario :To activate the plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('activate the plugin.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $I->seeElementInDOM(ConstantsPage::$plugin);
    $I->click(ConstantsPage::$plugin);
    $I->amOnPage('/wp-admin/plugins.php?plugin_status=inactive');

    $I->seeInSource('<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>');

    $setup = new SetupPage($I);
    $setup->activatePlugin($I);

    $I->amOnPage('/wp-admin/plugins.php?plugin_status=active');
    $I->seeElementInDOM('tr[data-slug="buddypress-media"] .row-actions span.deactivate a');

    echo "Plugin is activated!";

?>
