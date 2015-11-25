<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

$messages = [];
//echo new_payment(1, 2, 5, "Placeholder");
?>
<div class="transaction_type">
    <?php require('new_inventory_purchase.php'); ?>
</div>

<div class="transaction_type">
    <?php require('new_upgrade.php'); ?>
</div>

<div style='float:left; margin-left:2em'>
<?php
    foreach($messages as $message) {
        echo "\n" . __($message, "MicroBalance") . "<br>";
    }
?>
</div>