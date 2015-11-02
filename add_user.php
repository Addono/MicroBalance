<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

$firstname = format($_POST['firstname']);
$lastname = format($_POST['lastname']);
?>

<h1>Add new user</h1>

<?php
if($firstname=="") { 
    ?>
<form method="post">
    <p>First name</p>
    <input type="text" name="firstname" />
    <p>Last name</p>
    <input type="text" name="lastname" value="<?php echo $lastname; ?>"/>
    <?php submit_button(); ?>
</form>

<?php if($lastname!="") {?>
<p><b>First name cannot be empty.</b></p>
<?php }
} else {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mb_users';
    
    // Store the data in the database
    $wpdb->insert($table_name, array(
            'firstname' => $firstname,
            'lastname' => $lastname
        )
    );
    
    $name = $firstname;
    if($lastname != "") {
        $name .= " $lastname";
    }
    
    echo "<p>$name added.</p>";
    echo "<form method='post'>\n";
    submit_button("Add another one");
    echo "</form>";
}
?>
