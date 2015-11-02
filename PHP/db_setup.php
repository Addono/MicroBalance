<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

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
        UNIQUE KEY id (id)
    ) $charset_collate;
    
    CREATE TABLE $transactions_table_name (
        cdate datetime DEFAULT NOW() NOT NULL,
        targetid mediumint NOT NULL,
        authorid mediumint NOT NULL,
        amount float(5,2) NOT NULL,
        description tinytext,
        type ENUM('purchase', 'decleration', 'upgrade', 'refund', 'unknown', 'error') DEFAULT 'error' NOT NULL,
        state ENUM('new', 'unapproved', 'confirmed', 'finished', 'error', 'canceled') DEFAULT 'error' NOT NULL
    ) $charset_collate;
    ";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
?>