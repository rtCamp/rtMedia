<?php
namespace Page;

class Setup
{
    // include url of current page

    public static $allPluginsUrl = '/wp-admin/plugins.php?plugin_status=all';

    public static function route($param)
    {
        return static::$URL.$param;
    }

    public function activatePlugin($I){

        $I->scrollTo('.wp-list-table.plugins tr[data-slug="buddypress-media"]');
        $I->seeElementInDOM('tr[data-slug="buddypress-media"] .row-actions span.activate a');
        $I->click('tr[data-slug="buddypress-media"] .row-actions span.activate a');
        $I->wait(5);

    }

    public function deactivatePlugin($I){

        $I->scrollTo('.wp-list-table.plugins tr[data-slug="buddypress-media"]');
        $I->seeElementInDOM('tr[data-slug="buddypress-media"] .row-actions span.deactivate a');
        $I->click('tr[data-slug="buddypress-media"] .row-actions span.deactivate a');
        $I->wait(5);

    }

    public function uploadAndInstallPlugin($I){

        $I->see('Add New');
        $I->seeElement('a.page-title-action');
        $I->click('a.page-title-action');
        $I->wait(10);
        $I->seeInCurrentUrl('/wp-admin/plugin-install.php');
        $I->see('Upload Plugin');
        $I->seeElementInDOM('a.upload-view-toggle');
        $I->click('a.upload-view-toggle');
        $I->wait(3);
        $I->seeElementInDOM('input#pluginzip');
        $I->attachFile('input#pluginzip','rtmedia-ratings.zip');
        $I->wait(5);
        $I->seeElementInDOM('input#install-plugin-submit');
        $I->click('input#install-plugin-submit');
        $I->wait(5);
        $I->seeInCurrentUrl('/wp-admin/update.php?action=upload-plugin');

    }

    public function searchAndInstallPlugin($I){

        $I->see('Add New');
        $I->seeElement('a.page-title-action');
        $I->click('a.page-title-action');
        $I->wait(10);
        $I->seeInCurrentUrl('/wp-admin/plugin-install.php');
        $I->see('Upload Plugin');
        $I->seeElementInDOM('input.wp-filter-search');
        $I->fillField('input.wp-filter-search','rtMedia');
        $I->wait(5);
        $I->see('rtMedia for WordPress, BuddyPress and bbPress');
        $I->seeElementInDOM('#the-list > div.plugin-card.plugin-card-buddypress-media > div.plugin-card-top > div.action-links > ul > li:nth-child(1) > a');
        $I->click('#the-list > div.plugin-card.plugin-card-buddypress-media > div.plugin-card-top > div.action-links > ul > li:nth-child(1) > a');
        $I->wait(10);
        $I->see('Activate');
        $I->click('#the-list > div.plugin-card.plugin-card-buddypress-media > div.plugin-card-top > div.action-links > ul > li:nth-child(1) > a');
        $I->wait(10);
        $I->amOnPage('wp-admin/plugins.php?plugin_status=active');
        $I->see('rtMedia for WordPress, BuddyPress and bbPress');

    }

    public function removePlugin($I){

        self::deactivatePlugin($I);

        $I->amOnPage('/wp-admin/plugins.php?plugin_status=inactive');
        $I->wait(5);

        $I->scrollTo('.wp-list-table.plugins tr[data-slug="buddypress-media"]');
        $I->seeElementInDOM('tr[data-slug="buddypress-media"] .row-actions span.delete a');
        $I->click('tr[data-slug="buddypress-media"] .row-actions span.delete a');
        $I->wait(3);
        $I->acceptPopup();
        $I->wait(5);
        $I->amOnPage(self::$allPluginsUrl);
        $I->dontSeeInSource('<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>');

    }

}
