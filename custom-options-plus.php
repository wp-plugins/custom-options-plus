<?php
/*
Plugin Name: Custom Options Plus
Plugin URI: http://leocaseiro.com.br/custom-options-plus
Description: With this plugin, you can enter your custom options datas. It is very easy to install and use. Even if you do not have expertise in PHP.
You can for example, register the address and phone numbers of your company to leave in the header of your site. So, if someday relocate, you do not need to change your theme. Just change administratively.
You can also enter the login of your social networks. How to login twitter, Facebook, Youtube, contact email and more.
Version: 1.0
Author: Leo Caseiro
Author URI: http://leocaseiro.com.br/
*/

/*  Copyright 2011 Leo Caseiro (http://leocaseiro.com.br/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'COP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'COP_PLUGIN_NAME', trim( dirname( COP_PLUGIN_BASENAME ), '/' ) );
define( 'COP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . COP_PLUGIN_NAME );
define( 'COP_PLUGIN_URL', WP_PLUGIN_URL . '/' . COP_PLUGIN_NAME );

global $wpdb;
define( 'COP_TABLE',  $wpdb->prefix . 'custom_options_plus' );

//Create a table in MySQL database when activate plugin
function cop_setup() {
	global $wpdb;
	$wpdb->query('
		CREATE TABLE IF NOT EXISTS ' . COP_TABLE . ' (
		  `id` int(5) NOT NULL AUTO_INCREMENT,
		  `label` varchar(100) NOT NULL,
		  `name` varchar(80) NOT NULL,
		  `value` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	' );
}
register_activation_hook( __FILE__, 'cop_setup' );


//Create a Menu Custom Options Plus in Settings
add_action('admin_menu', 'cop_add_menu');
function cop_add_menu() {

 	global $my_plugin_hook;
 	$my_plugin_hook = add_options_page('Custom Options Plus', 'Custom Options Plus', 'manage_options', 'custom_options_plus', 'custom_options_plus_adm');

}

function cop_insert() {
	global $wpdb;
	
	$_POST['label'] = filter_var($_POST['label'], FILTER_SANITIZE_SPECIAL_CHARS);
	$_POST['name'] 	= filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	$_POST['value'] = filter_var($_POST['value'], FILTER_UNSAFE_RAW);	
	
	return $wpdb->insert( 
		COP_TABLE, 
		array( 
			'label' => $_POST['label'], 
			'name' => $_POST['name'],
			'value' => $_POST['value']
		)
	);
}

function cop_update() {
	global $wpdb;
	
	$_POST['id'] 	= filter_var($_POST['id'], FILTER_VALIDATE_INT);
	$_POST['label'] = filter_var($_POST['label'], FILTER_SANITIZE_SPECIAL_CHARS);
	$_POST['name'] 	= filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	$_POST['value'] = filter_var($_POST['value'], FILTER_UNSAFE_RAW);
	
	return $wpdb->update(
		COP_TABLE, 
		array( 
			'label' => $_POST['label'], 
			'name' 	=> $_POST['name'],
			'value' => $_POST['value']
		),
		array ('id' => $_POST['id'])
	);

}

function cop_delete( $id ) {
	global $wpdb;
	
	return $wpdb->query($wpdb->prepare('DELETE FROM ' . COP_TABLE . ' WHERE id = \'%d\' ', $id) );
}

function cop_get_options() {
	global $wpdb;
	
	return $wpdb->get_results('SELECT * FROM ' . COP_TABLE . ' ORDER BY label ASC');
}

function cop_get_option( $id ) {
	global $wpdb;
	
	return $wpdb->get_row('SELECT * FROM ' . COP_TABLE . ' WHERE id = ' . $id );
}

//Panel Admin
function custom_options_plus_adm() {
	global $wpdb, $my_plugin_hook;

	$id 	= '';
	$label 	= '';
	$name 	= '';
	$value 	= '';
	
	$message = '';
	
	if ( isset($_GET['del']) && $_GET['del'] > 0 ) :
		if ( cop_delete( $_GET['del'] ) ) :
			$message = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
		endif;
		
		
	elseif ( isset($_POST['id']) ) :
		
		if ($_POST['id'] == '') :
			cop_insert();
			$message = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';			
			
		elseif ($_POST['id'] > 0) :		
			cop_update();
			$message = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';			
			
		endif;
		
		
	elseif ( isset($_GET['id']) && $_GET['id'] > 0 ) :
		
		$option = cop_get_option( $_GET['id'] );
		
		$id 	= $option->id;
		$label 	= $option->label;
		$name 	= $option->name;
		$value 	= $option->value;
		
	endif;
	
	$options = cop_get_options();
?>
	
	<div class="wrap">
		<div id="icon-tools" class="icon32"></div><h2>Custom Options Plus</h2>
		
		<?php echo $message; ?>
		<br />
		<?php if ( count($options) > 0 ) : ?>
			<div class="wpbody-content">
				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<th>Label</th>
							<th>Name</th>
							<th>Value</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($options as $option ) : ?>
						<tr>
							<td><a title="Edit <?php echo $option->label; ?>" href="<?php echo preg_replace('/\\&.*/', '', $_SERVER['REQUEST_URI']); ?>&id=<?php echo $option->id; ?>"><?php echo $option->label; ?></a></td>
							<td><a title="Edit <?php echo $option->label; ?>" href="<?php echo preg_replace('/\\&.*/', '', $_SERVER['REQUEST_URI']); ?>&id=<?php echo $option->id; ?>"><?php echo $option->name; ?></a></td>
							<td><a title="Edit <?php echo $option->label; ?>" href="<?php echo preg_replace('/\\&.*/', '', $_SERVER['REQUEST_URI']); ?>&id=<?php echo $option->id; ?>"><?php echo $option->value; ?></a></td>
							<td><span class="trash"><a onclick="return confirm('Are you sure want to delete item?')" class="submitdelete" title="Delete <?php echo $option->label; ?>" href="<?php echo preg_replace('/\\&.*/', '', $_SERVER['REQUEST_URI']); ?>&del=<?php echo $option->id; ?>">Delete</a></span></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<br />
		<?php endif; ?>
		
		<form method="post" action="<?php echo preg_replace('/\\&.*/', '', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
			<h3>Add new Custom Option</h3>
			<table class="form-table">				
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="label">Label:</label>
						</td>
						<td>
							<input name="label" type="text" id="label" value="<?php echo $label; ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="name">*Name:</label>
						</td>
						<td>
							<input name="name" type="text" id="name" value="<?php echo $name; ?>" class="regular-text">
						</td>						
					</tr>
					<tr>
						<th scope="row">
							<label for="value">Value:</label>
						</td>
						<td>
							<textarea name="value" rows="7" cols="40" type="text" id="value" class="regular-text code"><?php echo $value; ?></textarea>
						</td>						
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"></p>
		</form>
		
	</div>
<?php
}



//get your single option
function get_custom( $name ) {
	global $wpdb;
	if ( '' != $name ) :
		return $wpdb->get_var( $wpdb->prepare( 'SELECT value FROM ' . COP_TABLE . ' WHERE name = \'%s\' LIMIT 1', $name ) );   
	else :
		return false;
	endif;
}

//get your array options
function get_customs( $name ) {
	global $wpdb;
	if ( '' != $name ) :
		$list = $wpdb->get_results( $wpdb->prepare( 'SELECT value FROM ' . COP_TABLE . ' WHERE name = \'%s\' ', $name ) , ARRAY_A);
		$array = array();
		foreach ( $list as $key => $name ) :
			$array[] = $name['value'];
		endforeach;
		return $array;
	else :
		return false;
	endif;
}


//Tutorial em Help Button
function cop_plugin_help($contextual_help, $screen_id, $screen) {

	global $my_plugin_hook;
	if ($screen_id == $my_plugin_hook) {

		$contextual_help = 'Used <em>get_custom(\'name\')</em> or <em>get_customs(\'name\')</em> in your theme.';
	}
	return $contextual_help;
}

add_filter('contextual_help', 'cop_plugin_help', 10, 3);