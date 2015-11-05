<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

$messages = [];
require('new_inventory_purchase.php');


?>
<div style='float:left; margin-left:2em'>
<?php
    foreach($messages as $message) {
        echo "\n" . __($message, "MicroBalance") . "<br>";
    }
?>
</div>