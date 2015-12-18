<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );
?>
<h1>Synchronizing user database</h1>
<?php
global $wpdb;

$table_name = $wpdb->prefix . 'mb_users';
	
$charset_collate = $wpdb->get_charset_collate();

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$users = get_users();

foreach($users as $user) {
    echo "<p>";
    //var_dump($user);
    echo $user->user_login;
   
}

$sql = "CREATE TABLE $table_name (
    id mediumint NOT NULL AUTO_INCREMENT,
    creationtime datetime DEFAULT NOW() NOT NULL,
    firstname tinytext NOT NULL,
    lastname tinytext,
    balance float(10,2) DEFAULT '0',
    UNIQUE KEY id (id)
    ) $charset_collate;";

//dbDelta( $sql );
?>