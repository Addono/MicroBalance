<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

$messages = [];

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-tabs');

// Get which tab should be shown by default.
switch($_GET['t']) {
    case "inventory_purchase":
    case "new_upgrade":
        $tab = "#" . $_GET['t'];
        break;
    default:
        $tab = false;
}
?>

<script>
    jQuery(document).ready(function() {
        // Create all tabs.
        jQuery("#tabs").tabs();
        
        <?php if($tab) { ?>
        // Switch to the selected tab.
        var index = jQuery('#tabs a[href="<?php echo $tab; ?>"]').parent().index();
        jQuery("#tabs").tabs("option", "active", index);
        <?php } ?>
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