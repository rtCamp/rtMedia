<?php

/**
* Scenario : Allow user to disable activity for media comments.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to disable activity for media comments.');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->disableSetting($I,ConstantsPage::$strActivityMediaCommentLabel,ConstantsPage::$activityMediaCommentCheckbox);

?>
