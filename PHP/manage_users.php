<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

echo "<h1>" . __('User manager', "MicroBalance") . "</h1>\n";

global $wpdb;
global $user_table_name;

$table_name = get_option('MB_user_table');
$charset_collate = $wpdb->get_charset_collate();
$messages = [];

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$users = get_users();

/*
 * @Description: Creates the table containing shoing information of all users.
 */
echo "<table>\n";

$colums = [
    __('ID', "MicroBalance"),
    __('Login name', "MicroBalance"),
    __('Email', "MicroBalance"),
    __('First name', "MicroBalance"),
    __('Last name', "MicroBalance"),
    __('Role', "MicroBalance"),
    __('Balance', "MicroBalance"),
    __('Till balance', "MicroBalance"),
    _x('Add', "add-money", "MicroBalance")
];

foreach($colums as $colum) {
    echo "\t<td><b>$colum</b></td>\n";
}

foreach($users as $user) {
    echo "<tr>\n";
    
    $sql = "SELECT * FROM $table_name WHERE id = '$user->ID';";
    $result = $wpdb->get_results($sql);
    
    if(count($result) > 1) {
        $message[] = "[ERROR] Multiple entries for $user->ID";
    } else {
        if(count($result) == 0) { // If the user doesn't exist in the MB user table, create it.
            $message = add_MB_user($user);
            $messages[] = "[INFO] User " . $message . "added";
            $result = $wpdb->get_results($sql);
        }
        
        // Stores all the data of the content of cells in every row.
        $cells = [
            [$user->ID, false],
            [$user->user_login, false],
            [$user->user_email, 'wp-admin-edit', 'email'],
            [$user->user_firstname, 'wp-admin-add','first_name'],
            [$user->user_lastname, 'wp-admin-add', 'last_name'],
            [ucfirst(__($result[0]->role,"MicroBalance")), false],
            ["&euro;" . $result[0]->debit, false],
            ["&euro;" . $result[0]->credit, false],
            [""]
        ];
        
        // Create all the cells from the array
        foreach($cells as $cell) {
            echo "<td>";
            if($cell[0] != "")  {
                if($cell[1] == 'wp-admin-edit' || $cell[1] == 'wp-admin-add') {
                    echo "<a href ='" . admin_url() . "user-edit.php?user_id=" . $user->ID . "#" . $cell[2] . "'>$cell[0]</a>";
                } else {
                    echo $cell[0];
                }
            } else {
                switch($cell[1]) {
                    case 'wp-admin-add':
                        redirect_button(_x("Add", "add-data", "MicroBalance"),admin_url() . "user-edit.php?user_id=" . $user->ID . "#" . $cell[2]);
                        break;
                    
                    case 'add':
                        
                        break;
                    
                    default:
                        echo "<td>";
                        break;
                }
            }
        }
        
        echo "</td>\n";
    }
    
    echo "</tr>\n";
}

echo "</table>\n<br>\n";

redirect_button(__("New user","MicroBalance"), admin_url() . "user-new.php", 'primary');

function add_MB_user($user) {
    global $wpdb;
    global $table_name;
    
    echo $user_table_name;
    
    $id = $user->ID;
    $firstname = $user->user_firstname;
    $lastname = $user->user_lastname;
    $nickname = $user->user_login;
    
    $userdata = [
        'id' => $id,
        'firstname' => $firstname,
        'lastname' => $lastname
            ];
            
    $wpdb->insert($table_name,$userdata);
    
    $user_add_message = "";
    
    if($firstname != "") {
        $user_add_message .= $firstname . " ";
    }
            
    if($lastname != "") {
            $user_add_message .= $lastname . " ";
        }
        
    if($nickname != "") {
            $user_add_message .= "($nickname) ";
        }
            
    return $user_add_message;
}

/*
 * @Description: Shows all messages generated earlier.
 */
$amount = count($messages);

if($amount != 0) {
    echo "<h1>" . sprintf(_n("%s message","%s messages", $amount, "MicroBalance"), $amount ) . "</h1>\n";
    
    foreach($messages as $message) {
        echo "$message<br>\n";
    }
}
?>
