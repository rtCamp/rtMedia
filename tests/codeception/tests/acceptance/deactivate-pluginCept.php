<?php

/**
* Scenario :To deactivate the plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Deactivate the plugin.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $I->seeElementInDOM(ConstantsPage::$plugin);
    $I->click(ConstantsPage::$plugin);
    $I->amOnPage('/wp-admin/plugins.php?plugin_status=active');

    $I->seeInSource('<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>');

    $setup = new SetupPage($I);
    $setup->deactivatePlugin($I);

    $I->amOnPage('/wp-admin/plugins.php?plugin_status=inactive');
    $I->seeElementInDOM('tr[data-slug="buddypress-media"] .row-actions span.activate a');

    echo "Plugin is deactivated!";

?>
