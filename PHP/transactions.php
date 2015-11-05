<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

$ids = explode(':', $_GET['id']);

if($ids[0] == "") {
    $result = $wpdb->get_results("SELECT * FROM " . get_table('transactions') . " ORDER BY transactionid DESC LIMIT 20");
    
    if($result[0] == "") {
        echo "<h2><i>" . __('No transactions found.', 'MicroBalance') . "</i></h2>\n";
    } else {
        echo "<p><i>" . sprintf(_n('Only one transaction found.', 'Showing the latest %s transactions.', 'MicroBalance'), count($result)) . "</i></p><br>\n";
        
        foreach($result as $id) {
            $ids[] = $id->transactionid;
        }
    }
    
}

foreach($ids as $id) {
    
    echo "<div class='transaction'>\n";
    echo_transaction($id, "h1");
    
    $journals = $wpdb->get_results("SELECT * FROM " . get_table('journal') . " WHERE transactionid = '$id'");
    
    foreach($journals as $journal) {
        echo "<div class='journal'>\n";
        echo_journal_entry($journal->journalid, 'h2');
        echo "</div>\n";
    }
    
    echo "</div>\n";
}
?>