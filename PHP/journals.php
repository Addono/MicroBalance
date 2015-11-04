<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

$ids = explode(':', $_GET['id']);

foreach($ids as $id) {
    echo_journal_entry($id);
}
?>