<?php
/**
 * Plugin Name: iPhyse WooCommerce custom email
 * Plugin URI: https://github.com/iPhyse/WPWC-Custom-mails
 * Description: Custom email plug-in for WooCommerce
 * Author: iPhyse
 * Author URI: https://github.com/iPhyse
 * Version: 0.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function trigger_status($order_id){

    // Use of the global Wordpress Database.
    global $wpdb;
	
	// Get the customer e-mail
    $results = (array) $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix  . "postmeta WHERE meta_key = '_billing_email' AND post_id = " . $order_id);
    $email = (array) $results[0];

    $order = new WC_Order($order_id);
    $orderstatus = (array) $order;

	// Include the email template class
    require_once('includes/class-template-email.php');
	
	// Send the custom mail on the custom status, if the order status has been changed. 
    switch($orderstatus["status"]){
        case    'my_custom_status':
            $data = (array) $wpdb->get_results("SELECT * FROM " . $wpdb->prefix  . "custom_mails WHERE mail_type = 'order-pending'");
            $data = (array) $data[0];
            new Custom_Order_Status_Email($email['meta_value'], $data['mail_title'], nl2br($data['mail_content']));
            break;
    }
}

function trigger_plugin_activated(){

    // Use of the global Wordpress Database.
    global $wpdb;
	
	// Define your custom table name
    $table_name = $wpdb->prefix . "custom_mails";
	
	// Check if the table already exists, if not, create the new table
    if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
        $sql = 'CREATE TABLE '.$table_name.'(
          id int NOT NULL AUTO_INCREMENT,
          mail_type tinytext NOT NULL,
          mail_title tinytext NOT NULL,
          mail_content longtext NOT NULL,
          UNIQUE KEY id (id)
        )';
		
        // Reference to upgrade.php file
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
	// Execute the create query
        dbDelta( $sql );

	// Insert default mail data
        $wpdb->insert( $table_name, array( 'mail_type' => 'order-new', 'mail_title' => 'new order title', 'mail_content' => 'My mail body text' ) );
    }

	// Add a custom order status to 'shop_order_status', slug will be created as 'a custom status name' => 'a_custom_status_name'. This will also be your trigger.
	// wp_insert_term( 'a custom status name', 'shop_order_status' );
	wp_insert_term( 'My custom status', 'shop_order_status' );

}

function trigger_plugin_deactivated(){

	// Remove, if exist, the custom added order status(es)
        $term = get_term_by( 'name', 'My custom status', 'shop_order_status' );
        if ( $term ) {
            wp_delete_term( $term->term_id, 'shop_order_status' );
        }
		
}

function cm_plugin_settings() {

    add_menu_page('Custom mail Settings', 'Custom mail Settings', 'administrator', 'cm_settings', 'cm_display_settings');
	
}

function cm_display_settings() {

    // Use of the global Wordpress Database.
    global $wpdb;
	
    if(isset($_POST['update'])){
        
		try {
			/*
			*	//If you cant get around with the $wpdb, Speak directly to the database like:
			*	$db_host = DB_HOST; $db_name = DB_NAME; $db_user = DB_USER; $db_pswd = DB_PASSWORD;
			*	$conn = new PDO("mysql:host=$db_host; dbname=$db_name", $db_user, $db_pswd);
			*	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			*/
			
			//Custom query and execution..
			
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

	// Get the mail data object from the table and convert it to a simple usable Array
    $custom_mail_status = (array) $wpdb->get_results("SELECT * FROM " . $wpdb->prefix  . "custom_mails WHERE mail_type = 'order-new'");
	// Get the needed data, [0] = the right array
    $custom_mail_status = (array) $order_pending[0];

	// Mark-up the HTML body for the menu page.
    echo '</pre>
        <div class="wrap">
			<form action="" method="post" name="options">
			<p>
				Email title:<br />
				<input name=\'mail_title\' style=\'width:800px\' type=\'text\' value=\'' . $order_new['mail_title'] . '\' />
				</p><p>
				Email body:<br />
				<textarea name=\'mail_content\' style=\'width:800px; height:250px\'>' . $order_new['mail_content'] . '
				</textarea>
			</p>
			<input type=\'hidden\' name=\'type\' value=\'new\'/>
			<input type=\'submit\' name=\'update\' value=\'Save!\' />
			</form>
        </div>
    <pre>';
}

// Hook/trigger when a order status has physically been changed 
add_action('woocommerce_order_status_changed', 'trigger_status' );
// Load plugin to the Admin menu
add_action('admin_menu', 'cm_plugin_settings');
// Execute 'trigger_plugin_activated' when the plugin is activated, prepare Wordpress and the database for use of this plugin
register_activation_hook(   __FILE__, 'trigger_plugin_activated' );
// Execute 'trigger_plugin_deactivated', when the plugin is deactivated, remove unused items
register_deactivation_hook( __FILE__, 'trigger_plugin_deactivated' );

