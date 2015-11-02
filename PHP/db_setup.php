<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

/*
 * @Description: Create necessary tables
 */

$user_table_name = $wpdb->prefix . 'mb_users';
$transactions_table_name = $wpdb->prefix . 'mb_transactions';
	
$charset_collate = $wpdb->get_charset_collate();

$sql = "
    CREATE TABLE $user_table_name (
        id mediumint NOT NULL,
        cdate datetime DEFAULT NOW() NOT NULL,
        firstname tinytext NOT NULL,
        lastname tinytext,
        balance float(10,2) DEFAULT '0' NOT NULL,
        till float(10,2) DEFAULT '0' NOT NULL,
        role ENUM('till manager', 'user', 'till') DEFAULT 'user' NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;
    
    CREATE TABLE $transactions_table_name (
        cdate datetime DEFAULT NOW() NOT NULL,
        sourceid mediumint NOT NULL,
        targetid mediumint NOT NULL,
        authorid mediumint NOT NULL,
        amount float(5,2) NOT NULL,
        description tinytext,
        type ENUM('purchase', 'decleration', 'payout', 'refund', 'unknown', 'error') DEFAULT 'error' NOT NULL,
        state ENUM('new', 'unapproved', 'confirmed', 'finished', 'error', 'canceled') DEFAULT 'error' NOT NULL,
        method ENUM('internal', 'cash', 'deposit')
    ) $charset_collate;
    ";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

/*
 * @Description: Create 'till user' data if it doesn't exist yet.
 */
$result = $wpdb->get_results("SELECT * FROM $user_table_name WHERE id = '0';");

if(count($result) == 0) {
    $till_data = [
        id => 0,
        role => 'till',
        firstname => 'till'
    ];
    
    $wpdb->insert($user_table_name, $till_data);
}
?>