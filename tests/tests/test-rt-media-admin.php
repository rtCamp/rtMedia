<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Test_RT_Media_Admin
 *
 * @author sanket
 */
class Test_RT_Media_Admin extends RT_WP_TestCase {

    var $rtmedia_admin;
    
    /**
	 * Setup Class Object and Parent Test Suite
	 */
	public function setUp() {
        parent::setUp();
        
        $this->rtmedia_admin = new RTMediaAdmin();
    }
    
    /*
     * Test plugin_add_settings_link
     */
    public function test_plugin_add_settings_link() {
        $links_array = array( '<a href="http://example.org/wp-admin/admin.php?page=rtmedia-settings">Settings</a>', '<a href="http://example.org/wp-admin/admin.php?page=rtmedia-support">Support</a>', );
        $response = $this->rtmedia_admin->plugin_add_settings_link( array() );
        $this->assertEquals( $links_array, $response );
    }
}
