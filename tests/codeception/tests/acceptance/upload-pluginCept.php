<?php

/**
* Scenario :To upload plugin.
*/
    use Page\Login as LoginPage;
    use Page\Setup as SetupPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Upload plugin.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $I->seeElementInDOM(ConstantsPage::$plugin);
    $I->click(ConstantsPage::$plugin);
    $I->wait(5);
    $I->seeInCurrentUrl('wp-admin/plugins.php');
    $I->dontSeeInSource('<strong>rtMedia 5 Star Ratings</strong>');

    $setup = new SetupPage($I);
    $setup->uploadAndInstallPlugin($I);

    $I->amOnPage('/wp-admin/plugins.php?plugin_status=inactive');
    $I->wait(5);

    $I->seeInSource('<strong>rtMedia 5 Star Ratings</strong>');

    echo "Plugin is uploaded!"

?>
