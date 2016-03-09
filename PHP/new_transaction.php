<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

$messages = [];
//echo new_payment(1, 2, 5, "Placeholder");
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-tabs');
?>

<script>
    jQuery(document).ready(function() {
       jQuery("#tabs").tabs();
    });
</script>
<div id="tabs">
    <ul>
        <li><a href="#inventory_purchase">inventory purchase</a>
        <li><a href="#new_upgrade" tooltip="Test">New upgrade</a>
    </ul>
    <div id="inventory_purchase" class="transaction_type">
        <?php require('new_inventory_purchase.php'); ?>
    </div>

    <div id="new_upgrade" class="transaction_type">
        <?php require('new_upgrade.php'); ?>
    </div>
</div>
<div style='float:left; margin-left:2em'>
<?php
    foreach($messages as $message) {
        echo "\n" . __($message, "MicroBalance") . "<br>";
    }
?>
</div>