<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

$ids = explode(':', $_GET['id']);

foreach($ids as $id) {
    echo_transaction($id, "h1");
    
    $journals = $wpdb->get_results("SELECT * FROM " . get_table('journal') . " WHERE transactionid = '$id'");
    
    foreach($journals as $journal) {
        echo_journal_entry($journal->journalid, 'h2');
    }
}
?>