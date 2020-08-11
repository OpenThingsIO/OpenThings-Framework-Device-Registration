<?php
/*
Plugin Name: OTF Device Registration
Plugin URI: https://github.com/openthingsio/OTF-Device-Registration
Description: Allows users to register devices for the OpenThings Framework.
Version: 0.0.0
Author: Matthew Oslan
License: GPL v3
*/


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


require_once realpath(dirname(__FILE__)) . '/otf-devices-page.php';

function activate_otf_device_registration() {
    create_devices_table();
}
register_activation_hook(__FILE__, 'activate_otf_device_registration');


function deactivate_otf_device_registration() {
}
register_deactivation_hook(__FILE__, 'deactivate_otf_device_registration');


// FIXME handle file read errors and database errors.
function create_devices_table() {
    global $wpdb;
    $schema = file_get_contents(realpath(dirname(__FILE__)) . '/schema.sql');
    // This assumes that schema.sql only contains a single statement.
    $wpdb->query($schema);
}
