<?php defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

$user = filter_input(INPUT_POST,'users', FILTER_VALIDATE_INT);

$amount = str_replace(',', '.', $_POST['amount']);
$description =  filter_input(INPUT_POST,'description',FILTER_SANITIZE_SPECIAL_CHARS);
$messages = [];
$proceed = false;

if($user != "" || $amount != "" || $decription != "") {
    if($amount < 0) {
        $messages[] = "Amount should be positive!";
    } elseif($amount == "") {
        $messages[] = "Amount is required!";
    } elseif(!is_numeric($amount)) {
        $messages[] = "Amount should be a number!. Use only numbers and if necessary one dot or comma.";
    } elseif($amount == 0) {
        $messages[] = "Amount can not be zero!";
    }
    
    if(count($messages) == 0) {
        $proceed = true;
        $transaction = new_inventory_purchase($user, $amount, $description, get_current_user_id());
        
        if($transaction > 0) {
            echo "<p>" . sprintf(__('Transaction %s succesfully added.', 'MicroBalance'), $transaction) . "</p>";
        }
    }
}
?>
<h1><?php _e("Inventory purchase", "MicroBalance"); ?></h1>
    <form method="post">
        <div style='float:left'>
            <h2><?php _e('Payed by', 'MicroBalance'); ?></h2>
                <?php get_user_selector(false, "users"); ?>
        </div>

        <div style='float:left; margin-left:2em'>
            <h2><?php _e('Amount', 'MicroBalance'); ?></h2>
            <input type='text' name='amount'><br>

            <h2><?php _e('Description', 'MicroBalance'); ?></h2>
            <textarea rows=10 name='description'><?php if(!$proceed) echo $_POST['description']; ?></textarea>
        </div>
    
        <div style='float:left; margin-left:2em'>
            <?php submit_button(__('Submit', 'MicroBalance')); ?>
        </div>
    </form>
        
<div style='float:left; margin-left:2em'>
<?php
    foreach($messages as $message) {
        echo "\n" . __($message, "MicroBalance") . "<br>";
    }
?>
</div>