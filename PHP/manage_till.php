<?php
// Prevent users from directly accessing this page.
defined( 'ABSPATH' ) or die( 'Not Even Close, Baby' );

global $wpdb;

echo "<h1>" . __("Till manager", "MicroBalance") . "</h1>\n";
echo "<table>\n";
echo "<tr><td><b>" . __("Inventory", "MicroBalance") . "</b><td>&euro;" . get_inventory() . "</td></tr>\n";
echo "<tr><td><b>" . __("Till", "MicroBalance") . "</b><td>&euro;" . get_till() . "</td></tr>\n";
echo "</table>\n";
?>