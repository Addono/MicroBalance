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


add_action('admin_head', 'hook_javascript');

function hook_javascript() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tooltip');
    ?>
<script>
    var MicroBalanceTooltips = jQuery( "[tooltip]" ).tooltip({
      position: {
        my: "left top",
        at: "right+5 top-5"
      }
    });
</script>
<?php }

add_action( 'admin_init', 'myplugin_scripts' ); // Enque style if user is in the admin panel.

function myplugin_scripts() {
    wp_register_style( 'jQuery-UI-style',  plugin_dir_url( __FILE__ ) . 'css/jquery-ui-black-tie.css' );
    wp_enqueue_style('jQuery-UI-style');
    
    wp_register_style( 'MB-style',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
    wp_enqueue_style( 'MB-style' );
}

add_action('admin_menu', 'plugin_menu');

function plugin_menu() {
    $primary_menu = "MicroBalance";
    add_utility_page(__('Overview', 'MicroBalance'), __('Overview', 'MicroBalance'), 'administrator', $primary_menu, 'home_page', 'dashicons-admin-generic');
    add_submenu_page($primary_menu, __('New transaction', 'MicroBalance'), __('New transaction', 'MicroBalance'), 'administrator', 'new-transaction', 'plugin_new_transactions');
    add_submenu_page($primary_menu, __('Transaction overview', 'MicroBalance'), __('Transaction overview', 'MicroBalance'), 'administrator', 'transactions', 'plugin_transactions');
}

function plugin_new_transactions() {
    require_once("PHP/new_transaction.php");
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
    
    if($amount <= 0 || $debtor_id == 0) { // Check if the inventory increases or if the till is the debtor.
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
    
    $transaction_id = new_transaction('inventory purchase', $description, $authorid);
    
    if($transaction_id < 0) {
        return -2;
    }
    
    echo $inventory_journal_id = new_journal($amount, 0, 'debit', $transaction_id);           // Create journal entry for the inventory.
    echo $debtor_journal_id = new_journal(-$amount, $debtor_id, 'credit', $transaction_id);   // Create journal entry for the debtor.
    
    // Check if both journals where created.
    if($inventory_journal_id > 0 && $debtor_journal_id > 0) {
        change_transaction_state($transaction_id, 'not payed');
    } else {
        echo "<p>Test</p>";
        change_transaction_state($transaction_id, 'error');
        return -3;
    }
    
    // Check if both journals where succesfully payed.
    if(pay_journal($inventory_journal_id) && pay_journal($debtor_journal_id)) {
        change_transaction_state($transaction_id, 'unapproved');
        return $transaction_id;
    } else {
        change_transaction_state($transaction_id, 'error');
        return -4;
    }
}

function new_payment($payed_by, $received_payment, $amount, $description = "", $authorid = "") {
    global $wpdb;
    $transaction_table = get_table('transaction');
    
    if($amount == 0 || $payed_by == 0 || $received_payment == 0) { // Till can't pay or receive directly. And money has to be transfered.
        return -1;
    }
    
    if($authorid == "") {
        $authorid = get_current_user();
    }
    
    if($amount > 0) {
        $type = 'upgrade';
    } else {
        $type = 'payout';
    }
    
    $transaction_id = new_transaction($type, $description, $authorid);
    
    if($transaction_id < 0) {
        return -2;
    }
    
    $payed_by_journal = new_journal($amount, $payed_by, 'debit', $transaction_id); 
    $received_payment_journal = new_journal($amount, $received_payment, 'credit', $transaction_id); 
    
    if($payed_by_journal > 0 && $received_payment_journal > 0) {
        change_transaction_state($transaction_id, 'not payed');
    } else {
        change_transaction_state($transaction_id, 'error');
        return -3;
    }
    
    if(pay_journal($payed_by_journal) && pay_journal($received_payment_journal)) {
        change_transaction_state($transaction_id, 'unapproved');
        return $transaction_id;
    } else {
        change_transaction_state($transaction_id, 'error');
        return -4;
    }
}

function new_transaction($type,$description = "", $authorid = "") {
    global $wpdb;
    
    // Check if the type is correct.
    switch($type) {
        case 'inventory purchase':
        case 'purchase':
        case 'decleration':
        case 'refund':
        case 'upgrade':
        case 'unknown':
            break;
        default:
            return -1;
    }
        
    if($authorid == "") {
        $authorid = get_current_user_id();
    }
    
    $data = [
        'type' => $type,
        'state' => 'new',
        'description' => $description,
        'authorid' => $authorid
    ];
    
     if($wpdb->insert(get_table('transaction'), $data) == null) {
         return -2;
     } else {
         return $wpdb->insert_id;
     }
}

function change_transaction_state($transaction_id, $state) {
    global $wpdb;
    
    return $wpdb->update(get_table('transaction'), array('state' => $state), array('transactionid' => $transaction_id));
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
        return $wpdb->update($journal_table, array('payed' => $date, 'new_balance' => $new_balance), array('journalid' => $journal_id));
    }
}

/*
 * @description: Returns the full name of the table.
 * @param table: Name of the table.
 * @returns: Full name of the table if found, false if it wasn't found.
 */
function get_table($table) {
    switch(strtolower($table)) {
        case 'user':
        case 'users':
            return get_option('MB_user_table');
        case 'transaction':
        case 'transactions':
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

function get_user_selector($exclude_self, $group_name) {
    global $wpdb;
    
    // Import jQuery and jQuery UI.
    wp_enqueue_script('jquery-ui-accordion');
    
    $users = get_MB_users(true,'id','asc');
    $id = get_current_user_id();
    $name = id_to_name($id);?>

<script>
    jQuery(document).ready(function() {
       jQuery("#<?php echo $group_name;?>_accordion").accordion({
           collapsible: true,
           heightStyle: 'content',
           animate: 100
       });
    });
</script>
    <?php
    if(!$exclude_self) {
        echo "<div id='$group_name" . "_accordion'>\n";
        echo "<h3>" . __('Yourself', 'MicroBalance') . "</h3>\n";
        echo "<div><label>\n\t<input type='radio' name='$group_name' value='$id' checked>$name\n</label></div>\n";
        echo "<h3><b>" . __('Other', 'MicroBalance') . "</b></h3>\n";
    } else {
        echo "<div id='$group_name" . "_accordion'>\n";
        echo "<h3><b>" . __('Users', 'MicroBalance') . "</b></h3>\n";
    }
    
    echo "<div>";
    if(count($users) == 0) {
        echo "<div><p>" . __("No other users found", "MicroBalance") . "</p></div>";
    } else {
        foreach($users as $user) {
            $name = id_to_name($user->id);

            echo "\n<div>\n\t<label><input type='radio' name='$group_name' value='$user->id'>$name</label>\n</div>\n";
        }
    }
    
    echo "</div></div>"; // Close the accordion's div.
}

function get_MB_users($exclude_self = true, $order = "id", $asc_desc = "ASC", $include_till = true) {
    global $wpdb;
    
    if(strtolower($asc_desc) != "asc" && strtolower($asc_desc) != "desc") {
        return -1;
    }

    $user_table = get_table('users');
    
    $sql = "SELECT * FROM $user_table";
    
    if($include_till || $exclude_self)  {
        $sql .= " WHERE ";
    }
    
    if($include_till) {
        $sql .= "id != '0'";
        if($exclude_self) {
            $sql .= " &&";
        }
    }
    
    if($exclude_self) {
        $id = get_current_user_id();
         $sql .= " id != '$id'";
    }
    
    switch($order) {
       case "id":
           $sql .= " ORDER BY id $asc_desc";
           break;
    }
    
    return $wpdb->get_results($sql);
}

/*
 * @description: Returns the full name of the user with the given ID.
 * @param id: ID from the user
 * @returns: Full name of the user with the parsed ID.
 */
function id_to_name($id) {
    global $wpdb;
    $table = $wpdb->prefix . "usermeta";
    
    $sql_firstname = "SELECT meta_value FROM $table WHERE user_id = '$id' && meta_key='first_name'";
    $sql_lastname = "SELECT meta_value FROM $table WHERE user_id = '$id' && meta_key='last_name'";
    
    $firstname = $wpdb->get_row($sql_firstname)->meta_value;
    $lastname = $wpdb->get_row($sql_lastname)->meta_value;
    
    if($firstname != "" && $lastname != "") {
        return $firstname . " " . $lastname;
    } else {
        return $firstname . $lastname;
    }
}

function transaction_type_to_text($type) {
    switch($type) {
        case 'inventory purchase':
            return __('Inventory purchase', 'MicroBalance');
        case 'purchase':
            return __('Purchase', 'MicroBalance');
        case 'decleration':
            return __('Declaration', 'MicroBalance');
        case 'payout':
            return __('Payout', 'MicroBalance');
        case 'refund':
            return __('Refund', 'MicroBalance');
        case 'unknown':
            return __('Unknown', 'MicroBalance');
        case 'error':
            return __('Error', 'MicroBalance');
    }
}

function echo_transaction($transaction_id, $header = "h2") {
    global $wpdb;
    $table = get_table('transactions');
    
    if(is_numeric($transaction_id) && is_integer($transaction_id + 0)) {
         echo "<$header>" . sprintf(__('Transaction %s', 'MicroBalance'), $transaction_id) . "</$header>\n";
         
         $sql = "SELECT * FROM $table WHERE transactionid = '$transaction_id'";
         
         $result = $wpdb->get_row($sql);
         
         if($result == null) {
             echo "<p>" . sprintf(__('Transaction %s not found.', 'MicroBalance'), $transaction_id) . "</p>";
             return false;
         } else {
             $description = $result->description == "" ? "-" : $result->description;
             
             $rows = [
                 __('Author','MicroBalance') => id_to_name($result->authorid),
                 __('Description', 'MicroBalance') => $description,
                 __('Type', 'MicroBalance') => transaction_type_to_text($result->type),
                 __('Added on', 'MicroBalance') => $result->cdate,
                 __('Edited on', 'MicroBalance') => $result->edate
             ];
             
             two_row_table($rows);
             
             return true;
         }
    }
}

function echo_journal_entry($journal_id, $header = "h2") {
    global $wpdb;
    $table = get_table('journal');
    
    echo "<$header>" . sprintf(__('Journal entry %s', 'MicroBalance'), $journal_id) . "</$header>\n";
    
    if(!is_numeric($journal_id) || !is_integer($journal_id + 0)) {
        echo "<p>" . __('Journal entry not found', 'MicroBalance') . "</p>\n";
        return false;
    } else {
        $result = $wpdb->get_row("SELECT * FROM $table WHERE journalid = '$journal_id'");
        
        if($result == null) {
            echo "<p>" . __('Journal entry not found', 'MicroBalance') . "</p>\n";
            return false;
        } else {
            if($result->accountid != 0) {
                $account = id_to_name($result->accountid);
            } else {
                if($result->cd == 'debit') {
                    $account = __('Inventory', 'MicroBalance');
                } else {
                    $account = __('Till', 'MicroBalance');
                }
            }
            
            $rows = [
                __('Account', 'MicroBalance') => $account . " ($result->accountid)",
                __('Amount', 'MicroBalance') => "&euro;" . $result->amount,
                __('Credit or debit', 'MicroBalance') => __(ucfirst($result->cd), 'MicroBalance'),
                __('Added on', 'MicroBalance') => $result->cdate,
                __('Edited on', 'MicroBalance') => $result->edate
            ];
            
            two_row_table($rows);
            
            return true;
        }
    }
}

function two_row_table($rows) {
    echo "<table>\n";
             
    foreach($rows as $title => $value) {
        echo "<tr>\n";
        echo "<td><b>$title</b><td>$value</td>";
        echo "</tr>\n";
    }
             
    echo "</table>\n";
}