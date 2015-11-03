<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

/**
 * Plugin Name: MicroBalance
 * Plugin URI: 
 * Description: Small till manager.
 * Version: 0.1.1
 * Author: Adriaan Knapen <a.d.knapen@student.tue.nl>
 * Author URI: 
 * License: 
 */

add_action('admin_menu', 'plugin_menu');

function plugin_menu() {
    $primary_menu = "MicroBalance";
    add_utility_page(__('Overview', 'MicroBalance'), __('Overview', 'MicroBalance'), 'administrator', $primary_menu, 'home_page', 'dashicons-admin-generic');
    add_submenu_page($primary_menu, __('Transactions', 'MicroBalance'), __('Transactions', 'MicroBalance'), 'administrator', 'transactions', 'plugin_transactions');
}

function plugin_transactions() {
    require_once("PHP/transactions.php");
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

function home_page() {
    require_once("PHP/manage_users.php");
    require_once("PHP/manage_till.php");
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

function new_inventory_purchase($debtor_id, $amount, $description = "", $authorid = "") {
    global $wpdb;
    $transaction_table = get_table('transaction');
    
    if($amount <= 0) {
        return -1; // Inventory value has to increase. Else throw error.
    }
    
    if($authorid == "") {
        $authorid = get_current_user_id();
    }
    
    $data = [
        'type' => 'inventory purchase',
        'state' => 'new',
        'description' => $description,
        'authorid' => $authorid
    ];
    
    $wpdb->insert($transaction_table,$data);
    
    $id = $wpdb->insert_id;
    
    $inventory_journal_id = new_journal($amount, 0, 'debit', $id);           // Create journal entry for the inventory.
    $debtor_journal_id = new_journal(-$amount, $debtor_id, 'credit', $id);   // Create journal entry for the debtor.
    
    if($inventory_journal_id > 0 && $debtor_journal_id > 0) {
        $data = [
            'state' => 'unapproved'
        ];
    } else {
        $data = [
            'state' => 'error'
        ];
    }
    
    $wpdb->update($transaction_table, $data, array('transactionid' => $id));
    
    if($data['state'] == 'error') {
        $wpdb->update($transaction_table,array('state' => 'error'), array('transactionid' => $id));
        return -2;
    }
    
    if(!pay_journal($inventory_journal_id) || !pay_journal($debtor_journal_id)) {
        change_transaction_state($id, 'error');
        return -3;
    } else {
        change_transaction_state($id, 'unapproved');
        return 1;
    }
}

function change_transaction_state($id, $state) {
    global $wpdb;
    return $wpdb->update($transaction_table,array('state' => $state), array('transactionid' => $id));
}

function set_transaction_state($state) {
    $data = [
        'state' => $state
    ];
    $wpdb->update($transaction_table, $data, array('transactionid' => $id));
}

/*
 * @Desciprion: Creates a new journal entry
 * 
 * @param amount: The value which gets transfered.
 * @param account: Which account ID changes.
 * @param cd: 'credit' or 'debit'.
 * param transactionID : ID of the transaction.
 * 
 * @return: journalID on succes, -1 if row insertion failed, -2 if cd wasn't set properly.
 */
function new_journal($amount, $account, $cd, $transactionID) {
    global $wpdb;
    $journal_table = get_table('journal');
    
    if($cd != 'credit' && $cd != 'debit') {
        return -2;
    }

    $data = [
        'amount' => $amount,
        'accountid' => $account,
        'cd' => $cd,
        'transactionid' => $transactionID
    ];
    
    if(!$wpdb->insert($journal_table, $data)) {
        return -1;
    }
    
    return $wpdb->insert_id;
}

function pay_journal($journal_id) {
    global $wpdb;
    $journal_table = get_table('journal');
    $users_table = get_table('users');
    
    $sql = "SELECT accountid, cd, amount, payed FROM $journal_table WHERE journalid = $journal_id";
    $journal_result = $wpdb->get_row($sql);
    
    if($journal_result == null) {
        return -1; // Throw an error if the journal entry wasn't found.
    }
    
    if($journal_result->payed != null) {
        return 0; // Abort if the journal was already payed.
    }
    
    $cd = $journal_result->cd;
    $user_id = $journal_result->accountid;
    
    $sql = "SELECT $cd FROM $users_table WHERE id = $user_id";
    $user_result = $wpdb->get_row($sql);
    
    if($user_result == null) {
        return -2; // Throw an error if the user wasn't found;
    }
    
    $old_balance = $user_result->$cd;
    $new_balance = $old_balance + $journal_result->amount;
    
    $data = [
        $cd => $new_balance
    ];
    
    if($wpdb->update($users_table, $data, array('id' => $user_id))) {
        $date = current_time('mysql', 0);
        return $wpdb->update($journal_table, array('payed' => $date), array('journalid' => $journal_id));
    }
}

/*
 * @description: Returns the full name of the table.
 * @param table: Name of the table.
 * @returns: Full name of the table if found, false if it wasn't found.
 */
function get_table($table) {
    switch($table) {
        case 'user':
        case 'users':
            return get_option('MB_user_table');
        case 'transaction':
            return get_option('MB_transaction_table');
        case 'journal':
            return get_option('MB_journal_table');
        default:
            return false;
    }
}

function get_till() {
    global $wpdb;
    
    $table = get_table('users');
    $sql = "SELECT credit FROM $table WHERE id = '0'";
    
    $result = $wpdb->get_row($sql);
    
    return $result->credit;
}

function get_inventory() {
    global $wpdb;
    
    $table = get_table('users');
    $sql = "SELECT debit FROM $table WHERE id = '0'";
    
    $result = $wpdb->get_row($sql);
    
    return $result->debit;
}