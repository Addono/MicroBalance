<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

/*
 * @Description: Create necessary tables
 */
$user_table_name = $wpdb->prefix . 'mb_users';
$transactions_table_name = $wpdb->prefix . 'mb_transactions';
$journal_table_name = $wpdb->prefix . 'mb_journal';

update_option('MB_user_table', $user_table_name);
update_option('MB_transaction_table', $transactions_table_name);
update_option('MB_journal_table', $journal_table_name);
	
$charset_collate = $wpdb->get_charset_collate();

$sql = "
    CREATE TABLE $user_table_name (
        id mediumint NOT NULL,
        firstname tinytext NOT NULL,
        lastname tinytext,
        debit float(10,2) DEFAULT '0' NOT NULL,
        credit float(10,2) DEFAULT '0' NOT NULL,
        role ENUM('till manager', 'user', 'till') DEFAULT 'user' NOT NULL,
        cdate datetime DEFAULT NOW() NOT NULL,
        edate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY id (id)
    ) $charset_collate;
    
    CREATE TABLE $transactions_table_name (
        transactionid mediumint PRIMARY KEY,
        authorid mediumint NOT NULL,
        description tinytext,
        type ENUM('purchase', 'decleration', 'payout', 'refund', 'unknown', 'error') DEFAULT 'error' NOT NULL,
        state ENUM('new', 'unapproved', 'confirmed', 'finished', 'canceled', 'error') DEFAULT 'error' NOT NULL,
        method ENUM('internal', 'cash', 'deposit'),
        cdate datetime DEFAULT NOW() NOT NULL,
        edate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $charset_collate;
        
    CREATE TABLE $journal_table_name (
        journalid mediumint PRIMARY KEY,
        transactionid mediumint NOT NULL,
        accountid mediumint NOT NULL,
        cd ENUM('credit','debit','error') DEFAULT 'error' NOT NULL,
        amount float(6,2) NOT NULL,
        cdate datetime DEFAULT NOW() NOT NULL,
        edate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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