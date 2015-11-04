<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

$ids = explode(':', $_GET['id']);
$table = get_table('transactions');

foreach($ids as $id) {
    if(is_numeric($id) && is_integer($id + 0)) {
         echo "<h2>" . __('Transaction', 'MicroBalance') . " $id</h2>\n";
         
         $sql = "SELECT * FROM $table WHERE transactionid = '$id'";
         
         $result = $wpdb->get_row($sql);
         
         if($result == null) {
             echo "<p>" . sprintf(__('Transaction %s not found.', 'MicroBalance'), $id) . "</p>";
         } else {
             $description = $result->description == "" ? "-" : $result->description;
             
             $rows = [
                 __('Author','MicroBalance') => id_to_name($result->authorid),
                 __('Description', 'MicroBalance') => $description,
                 __('Type', 'MicroBalance') => transaction_type_to_text($result->type),
                 __('Added on', 'MicroBalance') => $result->cdate,
                 __('Edited on', 'MicroBalance') => $result->edate
             ];
             echo "<table>\n";
             
             foreach($rows as $title => $value) {
                 echo "<tr>\n";
                 echo "<td><b>$title</b><td>$value</td>";
                 echo "</tr>\n";
             }
             
             echo "</table>\n";
         }
    }
}
?>