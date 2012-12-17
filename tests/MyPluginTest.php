<?php
/**
 * MyPlugin Tests
 */
class MyPluginTest extends WP_UnitTestCase {
    public $plugin_slug = 'buddypress-media';

    public function setUp() {
        parent::setUp();
        $this->my_plugin = 'buddypress-media';
    }

    public function testTrueStillEqualsTrue() {
        $this->assertTrue(true);
    }
}
