<?php

/**
* Scenario :To install plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Install plugin.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $I->seeElementInDOM(ConstantsPage::$plugin);
    $I->click(ConstantsPage::$plugin);
    $I->wait(5);
    $I->seeInCurrentUrl('wp-admin/plugins.php');

    $I->dontSeeInSource('<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>');

    $setup = new SetupPage($I);
    $setup->searchAndInstallPlugin($I);





?>
