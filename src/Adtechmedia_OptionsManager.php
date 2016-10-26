<?php

/*
    "WordPress Plugin Template" Copyright (C) 2016 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

class Adtechmedia_OptionsManager {

	public function get_option_name_prefix() {
		return get_class($this) . '_';
	}


	/**
	 * Define your options meta data here as an array, where each element in the array
	 *
	 * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
	 * key: an option name for the key (this name will be given a prefix when stored in
	 * the database to ensure it does not conflict with other plugin options)
	 * value: can be one of two things:
	 *   (1) string display name for displaying the name of the option to the user on a web page
	 *   (2) array where the first element is a display name (as above) and the rest of
	 *       the elements are choices of values that the user can select
	 * e.g.
	 * array(
	 *   'item' => 'Item:',             // key => display-name
	 *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
	 *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
	 *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
	 */
	public function get_option_meta_data() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_main_data() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_plugin_meta_data() {
		return array();
	}

	/**
	 * @return array of string name of options
	 */
	public function get_option_names() {
		return array_keys($this->get_option_meta_data());
	}

	/**
	 * Override this method to initialize options to default values and save to the database with add_option
	 *
	 * @return void
	 */
	protected function init_options() {
	}

	/**
	 * Cleanup: remove all options from the DB
	 *
	 * @return void
	 */
	protected function delete_saved_options() {
		$option_meta_data = $this->get_option_meta_data();
		if (is_array($option_meta_data)) {
			foreach ($option_meta_data as $a_option_key => $a_option_meta) {
				$prefixedOptionName = $this->prefix($a_option_key); // how it is stored in DB
				delete_option($prefixedOptionName);
			}
		}
	}

	/**
	 * @return string display name of the plugin to show as a name/title in HTML.
	 * Just returns the class name. Override this method to return something more readable
	 */
	public function get_plugin_display_name() {
		return get_class($this);
	}

	/**
	 * Get the prefixed version input $name suitable for storing in WP options
	 * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
	 *
	 * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
	 * @return string
	 */
	public function prefix( $name ) {
		$option_name_prefix = $this->get_option_name_prefix();
		if (strpos($name, $option_name_prefix) === 0) { // 0 but not false
			return $name; // already prefixed
		}
		return $option_name_prefix . $name;
	}

	/**
	 * Remove the prefix from the input $name.
	 * Idempotent: If no prefix found, just returns what was input.
	 *
	 * @param  $name string
	 * @return string $optionName without the prefix.
	 */
	public function &unPrefix( $name ) {
		$option_name_prefix = $this->get_option_name_prefix();
		if (strpos($name, $option_name_prefix) === 0) {
			return substr($name, strlen($option_name_prefix));
		}
		return $name;
	}

	/**
	 * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 *
	 * @param $option_name string defined in settings.php and set as keys of $this->optionMetaData
	 * @param $default string default value to return if the option is not set
	 * @return string the value from delegated call to get_option(), or optional default value
	 * if option is not set.
	 */
	public function get_option( $option_name, $default = null ) {
		$prefixed_option_name = $this->prefix($option_name); // how it is stored in DB
		$ret_val = get_option($prefixed_option_name);
		if (!$ret_val && $default) {
			$ret_val = $default;
		}
		return $ret_val;
	}

	/**
	 * @param $option_name
	 * @param null $default
	 * @return null
	 */
	public function get_plugin_option( $option_name, $default = null ) {
		global $wpdb;
		$ret_val = null;
		$table_name = $wpdb->prefix . Adtechmedia_Config::get('plugin_table_name');
		$row = $wpdb->get_row(
			$wpdb->prepare("SELECT option_value FROM $table_name WHERE option_name = %s LIMIT 1", $option_name)
		);

		if (is_object($row)) {
			$ret_val = $row->option_value;
		}

		if (!$ret_val && $default) {
			$ret_val = $default;
		}
		return $ret_val;
	}

	/**
	 * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 *
	 * @param  $option_name string defined in settings.php and set as keys of $this->optionMetaData
	 * @return bool from delegated call to delete_option()
	 */
	public function delete_option( $option_name ) {
		$prefixed_option_name = $this->prefix($option_name); // how it is stored in DB
		return delete_option($prefixed_option_name);
	}

	/**
	 * @param $option_name
	 * @return bool
	 */
	public function delete_plugin_option( $option_name ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Adtechmedia_Config::get('plugin_table_name');
		$result = $wpdb->delete($table_name, array( 'option_name' => $option_name ));
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 *
	 * @param  $option_mame string defined in settings.php and set as keys of $this->optionMetaData
	 * @param  $value mixed the new value
	 * @return null from delegated call to delete_option()
	 */
	public function add_option( $option_mame, $value ) {
		$prefixed_option_name = $this->prefix($option_mame); // how it is stored in DB
		return add_option($prefixed_option_name, $value);
	}

	/**
	 * @param $option_name
	 * @param $value
	 * @return bool
	 */
	public function add_plugin_option( $option_name, $value ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Adtechmedia_Config::get('plugin_table_name');
		$result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `$table_name` (`option_name`, `option_value`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)",
				$option_name,
				$value
			)
		);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 *
	 * @param  $option_name string defined in settings.php and set as keys of $this->optionMetaData
	 * @param  $value mixed the new value
	 * @return null from delegated call to delete_option()
	 */
	public function update_option( $option_name, $value ) {
		$prefixed_option_name = $this->prefix($option_name); // how it is stored in DB
		return update_option($prefixed_option_name, $value);
	}

	/**
	 * @param $option_name
	 * @param $value
	 * @return bool
	 */
	public function update_plugin_option( $option_name, $value ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Adtechmedia_Config::get('plugin_table_name');
		$result = $wpdb->update($table_name, [ 'option_value' => $value ], array( 'option_name' => $option_name ));
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
	 * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
	 * The idea is use an option to indicate what role level a user must minimally have in order to do some operation.
	 * So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above should be
	 * able to do Operation X.
	 * Also see: canUserDoRoleOption()
	 *
	 * @param  $option_name
	 * @return string role name
	 */
	public function get_role_option( $option_name ) {
		$role_allowed = $this->get_option($option_name);
		if (!$role_allowed || $role_allowed == '') {
			$role_allowed = 'Administrator';
		}
		return $role_allowed;
	}

	/**
	 * Given a WP role name, return a WP capability which only that role and roles above it have
	 * http://codex.wordpress.org/Roles_and_Capabilities
	 *
	 * @param  $role_name
	 * @return string a WP capability or '' if unknown input role
	 */
	protected function role_to_capability( $role_name ) {
		switch ($role_name) {
			case 'Super Admin':
				return 'manage_options';
			case 'Administrator':
				return 'manage_options';
			case 'Editor':
				return 'publish_pages';
			case 'Author':
				return 'publish_posts';
			case 'Contributor':
				return 'edit_posts';
			case 'Subscriber':
				return 'read';
			case 'Anyone':
				return 'read';
		}
		return '';
	}

	/**
	 * @param $role_name string a standard WP role name like 'Administrator'
	 * @return bool
	 */
	public function is_user_role_equal_or_better_than( $role_name ) {
		if ('Anyone' == $role_name) {
			return true;
		}
		$capability = $this->role_to_capability($role_name);
		return current_user_can($capability);
	}

	/**
	 * @param  $option_name string name of a Role option (see comments in getRoleOption())
	 * @return bool indicates if the user has adequate permissions
	 */
	public function can_user_do_role_option( $option_name ) {
		$role_allowed = $this->get_role_option($option_name);
		if ('Anyone' == $role_allowed) {
			return true;
		}
		return $this->is_user_role_equal_or_better_than($role_allowed);
	}

	/**
	 * see: http://codex.wordpress.org/Creating_Options_Pages
	 *
	 * @return void
	 */
	public function create_settings_menu() {
		$pluginName = $this->get_plugin_display_name();
		//create new top-level menu
		add_menu_page(
			$pluginName . ' Plugin Settings',
			$pluginName,
			'administrator',
			get_class($this),
			array( &$this, 'settings_page' )
		/*,plugins_url('/images/icon.png', __FILE__)*/
		); // if you call 'plugins_url; be sure to "require_once" it

		//call register settings function
		add_action('admin_init', array( &$this, 'register_settings' ));
	}

	/**
	 *
	 */
	public function register_settings() {
		$settings_group = get_class($this) . '-settings-group';
		$option_meta_data = $this->get_option_meta_data();
		foreach ($option_meta_data as $a_option_key => $a_option_meta) {
			register_setting($settings_group, $a_option_meta);
		}
	}

	/**
	 *
	 */
	public function update_prop() {
		$prop = Adtechmedia_Request::property_update(
			$this->get_plugin_option('id'),
			$this->get_plugin_option('container'),
			$this->get_plugin_option('selector'),
			$this->get_plugin_option('price'),
			$this->get_plugin_option('author_name'),
			$this->get_plugin_option('author_avatar'),
			$this->get_plugin_option('ads_video'),
			$this->get_plugin_option('key'),
			$this->get_plugin_option("content_offset"),
			$this->get_plugin_option("content_lock"),
			$this->get_plugin_option("revenue_method"),
			$this->get_plugin_option("payment_pledged"),
			$this->get_plugin_option("content_offset_type"),
			$this->get_plugin_option("price_currency"),
			$this->get_plugin_option("content_paywall")
		);
		Adtechmedia_ContentManager::clear_all_content();
	}

	/**
	 * Creates HTML for the Administration page to set options for this plugin.
	 * Override this method to create a customized page.
	 *
	 * @return void
	 */
	public function settingsPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'adtechmedia'));
		}

		$main_data = $this->get_main_data();
		$plugin_meta_data = $this->get_plugin_meta_data();
		$main_data_class = get_class($this) . '-main-settings-group';
		$plugin_meta_data_class = get_class($this) . '-data-settings-group';

		// Save Posted Options
		if (isset($_POST['option_page']) && $_POST['option_page'] == $main_data_class) {
			$this->try_to_save_post($main_data);
			$key = Adtechmedia_Request::api_key_create(
				$this->get_plugin_option('website_domain_name'),
				$this->get_plugin_option('website_url')
			);
			$this->add_plugin_option('key', $key);
			$prop = Adtechmedia_Request::property_create(
				$this->get_plugin_option('website_domain_name'),
				$this->get_plugin_option('website_url'),
				$this->get_plugin_option('support_email'),
				$this->get_plugin_option('country'),
				$key
			);
			$this->add_plugin_option('BuildPath', $prop['BuildPath']);
			$this->add_plugin_option('Id', $prop['Id']);
			$this->update_prop();
		} elseif (isset($_POST['option_page']) && $_POST['option_page'] == $plugin_meta_data_class) {

			$this->try_to_save_post($plugin_meta_data);
			$this->update_prop();
		}

		require_once 'views/admin.php';

		/*$this->settingForm(
			$main_data,
			__('Key Settings', 'adtechmedia'),
			__('Regenerate', 'adtechmedia'),
			$main_data_class
		);
		$this->settingForm(
			$plugin_meta_data,
			__('Settings', 'adtechmedia'),
			__('Save Changes', 'adtechmedia'),
			$plugin_meta_data_class
		);*/


	}

	/**
	 * @param $options
	 */
	public function try_to_save_post( $options ) {
		if ($options != null) {
			foreach ($options as $a_option_key => $a_option_meta) {
				if (isset($_POST[ $a_option_key ])) {
					$this->update_plugin_option($a_option_key, $_POST[ $a_option_key ]);
				}
			}
		}
	}

	public function setting_form( $option_meta_data, $title_text, $button_text, $settings_group ) {

		?>
		<div class="wrap">

			<h2><?php echo $this->get_plugin_display_name();
				echo ' ' . $title_text; ?></h2>

			<form method="post" action="">
				<?php settings_fields($settings_group); ?>
				<style type="text/css">
					table.plugin-options-table {
						width: 100%;
						padding: 0;
					}

					table.plugin-options-table tr:nth-child(even) {
						background: #f9f9f9
					}

					table.plugin-options-table tr:nth-child(odd) {
						background: #FFF
					}

					table.plugin-options-table tr:first-child {
						width: 35%;
					}

					table.plugin-options-table td {
						vertical-align: middle;
					}

					table.plugin-options-table td + td {
						width: auto
					}

					table.plugin-options-table td > p {
						margin-top: 0;
						margin-bottom: 0;
					}
				</style>
				<table class="plugin-options-table">
					<tbody>
					<?php
					if ($option_meta_data != null) {
						foreach ($option_meta_data as $a_option_key => $a_option_meta) {
							$display_text = is_array($a_option_meta) ? $a_option_meta[0] : $a_option_meta;
							?>
							<tr valign="top">
								<th scope="row"><p><label
											for="<?php echo $a_option_key ?>"><?php echo $display_text ?></label></p>
								</th>
								<td>
									<?php $this->create_form_control(
										$a_option_key,
										$a_option_meta,
										$this->get_plugin_option($a_option_key)
									); ?>
								</td>
							</tr>
							<?php
						}
					}
					?>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary"
						   value="<?= $button_text ?>"/>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Helper-function outputs the correct form element (input tag, select tag) for the given item
	 *
	 * @param  $a_option_key string name of the option (un-prefixed)
	 * @param  $a_option_meta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
	 * @param  $saved_option_value string current value for $aOptionKey
	 * @param  $placeholder
	 * @return void
	 */
	protected function create_form_control( $a_option_key, $a_option_meta, $saved_option_value, $placeholder = "" ) {
		if (is_array($a_option_meta) && count($a_option_meta) >= 2) { // Drop-down list
			$choices = array_slice($a_option_meta, 1);
			?>
			<select name="<?php echo $a_option_key ?>" id="<?php echo $a_option_key ?>">
				<?php
				foreach ($choices as $a_choice) {
					$selected = ($a_choice == $saved_option_value) ? 'selected' : '';
					?>
					<option
						value="<?php echo $a_choice ?>" <?php echo $selected ?>><?php echo $this->get_option_value_i18n_string(
							$a_choice
						) ?></option>
					<?php
				}
				?>
			</select>
			<?php

		} else { // Simple input field
			?>
			<input type="text" placeholder="<?= $placeholder ?>" name="<?php echo $a_option_key ?>"
				   id="<?php echo $a_option_key ?>"
				   value="<?php echo esc_attr($saved_option_value) ?>" size="100"/>
			<?php

		}
	}

	/**
	 * Override this method and follow its format.
	 * The purpose of this method is to provide i18n display strings for the values of options.
	 * For example, you may create a options with values 'true' or 'false'.
	 * In the options page, this will show as a drop down list with these choices.
	 * But when the the language is not English, you would like to display different strings
	 * for 'true' and 'false' while still keeping the value of that option that is actually saved in
	 * the DB as 'true' or 'false'.
	 * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
	 * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
	 * function, returning the "__()" i18n name of that string.
	 *
	 * @param  $option_value string
	 * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
	 */
	protected function get_option_value_i18n_string( $option_value ) {
		switch ($option_value) {
			case 'true':
				return __('true', 'adtechmedia');
			case 'false':
				return __('false', 'adtechmedia');
			case 'Administrator':
				return __('Administrator', 'adtechmedia');
			case 'Editor':
				return __('Editor', 'adtechmedia');
			case 'Author':
				return __('Author', 'adtechmedia');
			case 'Contributor':
				return __('Contributor', 'adtechmedia');
			case 'Subscriber':
				return __('Subscriber', 'adtechmedia');
			case 'Anyone':
				return __('Anyone', 'adtechmedia');
		}
		return $option_value;
	}

	/**
	 * Query MySQL DB for its version
	 *
	 * @return string|false
	 */
	protected function get_my_sql_version() {
		global $wpdb;
		$rows = $wpdb->get_results('select version() as mysqlversion');
		if (!empty($rows)) {
			return $rows[0]->mysqlversion;
		}
		return false;
	}

	/**
	 * If you want to generate an email address like "no-reply@your-site.com" then
	 * you can use this to get the domain name part.
	 * E.g.  'no-reply@' . $this->getEmailDomain();
	 * This code was stolen from the wp_mail function, where it generates a default
	 * from "wordpress@your-site.com"
	 *
	 * @return string domain name
	 */
	public function get_email_domain() {
		// Get the site domain and get rid of www.
		$sitename = strtolower($_SERVER['SERVER_NAME']);
		if (substr($sitename, 0, 4) == 'www.') {
			$sitename = substr($sitename, 4);
		}
		return $sitename;
	}
}

