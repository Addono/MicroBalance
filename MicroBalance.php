<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

/**
 * Plugin Name: MicroBalance
 * Plugin URI: 
 * Description: 
 * Version: 0.1.1
 * Author: Adriaan Knapen <a.d.knapen@student.tue.nl>
 * Author URI: 
 * License: 
 */
     
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
    //add_menu_page('MicroBalance Admin Panel', 'Microbalance Admin Panel', 'administrator', 'MicroBalance-settings', 'settings_page', 'dashicons-admin-generic');
    add_menu_page(__('Manage users', 'MicroBalance'), 'MicroBalance', 'administrator', 'MicroBalance-manage-users', 'manage_users', 'dashicons-admin-generic');
}
    
function settings_page() {
    ?>
<h1>MicroBalance Settings</h1>
<div class="wrap">
    <h2>Staff Details</h2>
        
    <form method="post" action="options.php">
    <?php settings_fields( 'my-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'my-plugin-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Accountant Name</th>
                <td><input type="text" name="accountant_name" value="<?php echo esc_attr( get_option('accountant_name') ); ?>" /></td>
            </tr>
                
            <tr valign="top">
                <th scope="row">Accountant Phone Number</th>
                <td><input type="text" name="accountant_phone" value="<?php echo esc_attr( get_option('accountant_phone') ); ?>" /></td>
            </tr>
                
            <tr valign="top">
                <th scope="row">Accountant Email</th>
                <td><input type="text" name="accountant_email" value="<?php echo esc_attr( get_option('accountant_email') ); ?>" /></td>
            </tr>
        </table>
            
    <?php submit_button(); ?>
        
    </form>
</div>
    <?php
    
}

function add_new_mb_user() {
    require_once("PHP/add_user.php");
}

add_action('admin_init', 'set_settings');

function set_settings() {
    register_setting( 'my-plugin-settings-group', 'accountant_name' );
    register_setting( 'my-plugin-settings-group', 'accountant_phone' );
    register_setting( 'my-plugin-settings-group', 'accountant_email' );
}

register_activation_hook(__FILE__, 'db_setup');

function db_setup() {
    require("PHP/db_setup.php");
}

function manage_users() {
    require_once("PHP/manage_users.php");
}

add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );

function my_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'MicroBalance', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Non hook-functions here

function format($string) {
    return ucfirst(strtolower($string));
}

// Returns a button which acts as link when clicked.
function redirect_button($title, $target = "", $type = "secondary") {
    echo "<form method='post' action='$target'>\n";
    submit_button($title, $type, 'submit', false);
    echo "\n</form>\n";
}