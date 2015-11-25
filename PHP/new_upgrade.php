<?php 
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby!' );

if($_POST['type'] == "upgrade") {

}
?>
<h1><?php _e("Register upgrade", "MicroBalance"); ?></h1>
    <form method="post">
        <div>
            <h2><?php _e('Payed by', 'MicroBalance'); ?></h2>
                <?php get_user_selector(false, "payed_by"); ?>
        </div>

        <div style='float:left'>
            <h2><?php _e('Payed to', 'MicroBalance'); ?></h2>
                <?php get_user_selector(false, "payed_to"); ?>
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
        <input type='hidden' name='type' value='inventory purchase'>
    </form>
        