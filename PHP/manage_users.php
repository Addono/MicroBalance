<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

$table_name = $wpdb->prefix . 'mb_users';
	
$charset_collate = $wpdb->get_charset_collate();

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$users = get_users();

foreach($users as $user) {
    echo "<p>";
    //var_dump($user);
    echo "'" . $user->user_login . "'   ";
    echo $user->ID;
    
    $result = $wpdb->get_results("SELECT * FROM wp_mb_users WHERE id = '$user->ID';");
    
    if(count($result) == 0) { // If the user doesn't exist in the MB user table, create it.
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
        
        echo "<p>$user_add_message added</p>";
    }
}
?>
