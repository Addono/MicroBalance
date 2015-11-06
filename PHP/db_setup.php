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
        debit float(10,2) DEFAULT '0' NOT NULL,
        credit float(10,2) DEFAULT '0' NOT NULL,
        role ENUM('till manager', 'user', 'till') DEFAULT 'user' NOT NULL,
        cdate datetime DEFAULT NOW() NOT NULL,
        edate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY id (id)
    ) $charset_collate;
    
    CREATE TABLE $transactions_table_name (
        transactionid mediumint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        authorid mediumint UNSIGNED NOT NULL,
        description tinytext,
        type ENUM('inventory purchase', 'purchase', 'decleration', 'payout', 'refund', 'upgrade', 'unknown', 'error') DEFAULT 'error' NOT NULL,
        state ENUM('new', 'unapproved', 'confirmed', 'finished', 'canceled', 'error', 'not payed') DEFAULT 'error' NOT NULL,
        cdate datetime DEFAULT NOW() NOT NULL,
        edate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) 
    $charset_collate
    ENGINE=InnoDB
    AUTO_INCREMENT=1000000
    ;
       
    CREATE TABLE `$journal_table_name` (
	`journalid` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`transactionid` MEDIUMINT(8) UNSIGNED NOT NULL,
	`accountid` MEDIUMINT(8) UNSIGNED NOT NULL,
	`cd` ENUM('credit','debit','error') NOT NULL DEFAULT 'error',
	`amount` FLOAT(6,2) NOT NULL,
        `payed` DATETIME DEFAULT NULL,
	`cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `edate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`journalid`)
    )
    $charset_collate
    ENGINE=InnoDB
    AUTO_INCREMENT=5000000
    ;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

$sql = "ALTER TABLE $transactions_table_name AUTO_INCREMENT=1000000;
    ALTER TABLE $journal_table_name AUTO_INCREMENT=5000000;";

dbDelta($sql);

/*
 * @Description: Create 'till user' data if it doesn't exist yet.
 */
$result = $wpdb->get_results("SELECT * FROM $user_table_name WHERE id = '0';");

if(count($result) == 0) {
    $till_data = [
        id => 0,
        role => 'till',
    ];
    
    $wpdb->insert($user_table_name, $till_data);
}
?>