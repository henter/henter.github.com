<?php
/**
 * WordPress Plugin Administration API
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * Parse the plugin contents to retrieve plugin's metadata.
 *
 * The metadata of the plugin's data searches for the following in the plugin's
 * header. All plugin data must be on its own line. For plugin description, it
 * must not have any newlines or only parts of the description will be displayed
 * and the same goes for the plugin data. The below is formatted for printing.
 *
 * <code>
 * /*
 * Plugin Name: Name of Plugin
 * Plugin URI: Link to plugin information
 * Description: Plugin Description
 * Author: Plugin author's name
 * Author URI: Link to the author's web site
 * Version: Must be set in the plugin for WordPress 2.3+
 * Text Domain: Optional. Unique identifier, should be same as the one used in
 *		plugin_text_domain()
 * Domain Path: Optional. Only useful if the translations are located in a
 *		folder above the plugin's base path. For example, if .mo files are
 *		located in the locale folder then Domain Path will be "/locale/" and
 *		must have the first slash. Defaults to the base folder the plugin is
 *		located in.
 * Network: Optional. Specify "Network: true" to require that a plugin is activated
 *		across all sites in an installation. This will prevent a plugin from being
 *		activated on a single site when Multisite is enabled.
 *  * / # Remove the space to close comment
 * </code>
 *
 * Plugin data returned array contains the following:
 *		'Name' - Name of the plugin, must be unique.
 *		'Title' - Title of the plugin and the link to the plugin's web site.
 *		'Description' - Description of what the plugin does and/or notes
 *		from the author.
 *		'Author' - The author's name
 *		'AuthorURI' - The authors web site address.
 *		'Version' - The plugin version number.
 *		'PluginURI' - Plugin web site address.
 *		'TextDomain' - Plugin's text domain for localization.
 *		'DomainPath' - Plugin's relative directory path to .mo files.
 *		'Network' - Boolean. Whether the plugin can only be activated network wide.
 *
 * Some users have issues with opening large files and manipulating the contents
 * for want is usually the first 1kiB or 2kiB. This function stops pulling in
 * the plugin contents when it has all of the required plugin data.
 *
 * The first 8kiB of the file will be pulled in and if the plugin data is not
 * within that first 8kiB, then the plugin author should correct their plugin
 * and move the plugin data headers to the top.
 *
 * The plugin file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 * @link http://trac.wordpress.org/ticket/5651 Previous Optimizations.
 * @link http://trac.wordpress.org/ticket/7372 Further and better Optimizations.
 * @since 1.5.0
 *
 * @param string $plugin_file Path to the plugin file
 * @param bool $markup If the returned data should have HTML markup applied
 * @param bool $translate If the returned data should be translated
 * @return array See above for description.
 */

function get_plugin_data( $plugin_file, $markup = true) {

	$default_headers = array(
		'Name' => 'App Name',
		'AppURI' => 'App URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
		'DomainPath' => 'Domain Path',
	);

	$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

	//For backward compatibility by default Title is the same as Name.
	$plugin_data['Title'] = $plugin_data['Name'];

	if ( $markup )
		$plugin_data = _get_plugin_data_markup( $plugin_file, $plugin_data, $markup);
	else
		$plugin_data['AuthorName'] = $plugin_data['Author'];

	return $plugin_data;
}

function _get_plugin_data_markup($plugin_file, $plugin_data, $markup = true) {
	$plugins_allowedtags = array(
		'a'       => array( 'href' => array(), 'title' => array() ),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'em'      => array(),
		'strong'  => array(),
	);

	$plugin_data['AuthorName'] = $plugin_data['Author'] = dc_kses( $plugin_data['Author'], $plugins_allowedtags );

	//Apply Markup
	if ( $markup ) {
		if ( ! empty($plugin_data['AppURI']) && ! empty($plugin_data['Name']) )
			$plugin_data['Title'] = '<a href="' . $plugin_data['AppURI'] . '" title="' .  'Visit plugin homepage'  . '">' . $plugin_data['Name'] . '</a>';
		else
			$plugin_data['Title'] = $plugin_data['Name'];

		if ( ! empty($plugin_data['AuthorURI']) && ! empty($plugin_data['Author']) )
			$plugin_data['Author'] = '<a href="' . $plugin_data['AuthorURI'] . '" title="' .  'Visit author homepage'  . '">' . $plugin_data['Author'] . '</a>';

		if ( ! empty($plugin_data['Author']) )
			$plugin_data['Description'] .= ' <cite>' . sprintf( 'By %s', $plugin_data['Author'] ) . '.</cite>';
	}

	// Sanitize all displayed data. Author and AuthorName sanitized above.
	$plugin_data['Title']       = dc_kses( $plugin_data['Title'],       $plugins_allowedtags );
	$plugin_data['Version']     = dc_kses( $plugin_data['Version'],     $plugins_allowedtags );
	$plugin_data['Description'] = dc_kses( $plugin_data['Description'], $plugins_allowedtags );
	$plugin_data['Name']        = dc_kses( $plugin_data['Name'],        $plugins_allowedtags );

	return $plugin_data;
}

/**
 * Get a list of a plugin's files.
 *
 * @since 2.8.0
 *
 * @param string $plugin Plugin ID
 * @return array List of files relative to the plugin root.
 */
function get_plugin_files($plugin) {
	$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
	$dir = dirname($plugin_file);
	$plugin_files = array($plugin);
	if ( is_dir($dir) && $dir != WP_PLUGIN_DIR ) {
		$plugins_dir = @ opendir( $dir );
		if ( $plugins_dir ) {
			while (($file = readdir( $plugins_dir ) ) !== false ) {
				if ( substr($file, 0, 1) == '.' )
					continue;
				if ( is_dir( $dir . '/' . $file ) ) {
					$plugins_subdir = @ opendir( $dir . '/' . $file );
					if ( $plugins_subdir ) {
						while (($subfile = readdir( $plugins_subdir ) ) !== false ) {
							if ( substr($subfile, 0, 1) == '.' )
								continue;
							$plugin_files[] = plugin_basename("$dir/$file/$subfile");
						}
						@closedir( $plugins_subdir );
					}
				} else {
					if ( plugin_basename("$dir/$file") != $plugin )
						$plugin_files[] = plugin_basename("$dir/$file");
				}
			}
			@closedir( $plugins_dir );
		}
	}

	return $plugin_files;
}

/**
 * Check the plugins directory and retrieve all plugin files with plugin data.
 *
 * WordPress only supports plugin files in the base plugins directory
 * (wp-content/plugins) and in one directory above the plugins directory
 * (wp-content/plugins/my-plugin). The file it looks for has the plugin data and
 * must be found in those two locations. It is recommended that do keep your
 * plugin files in directories.
 *
 * The file with the plugin data is the file that will be included and therefore
 * needs to have the main execution for the plugin. This does not mean
 * everything must be contained in the file and it is recommended that the file
 * be split for maintainability. Keep everything in one file for extreme
 * optimization purposes.
 *
 * @since 1.5.0
 *
 * @param string $plugin_folder Optional. Relative path to single plugin folder.
 * @return array Key is the plugin file path and the value is an array of the plugin data.
 */
function get_plugins($plugin_folder = '') {

	if ( ! $cache_plugins = cache::get('plugins', 'plugins') )
		$cache_plugins = array();

	if ( isset($cache_plugins[ $plugin_folder ]) )
		return $cache_plugins[ $plugin_folder ];

	$wp_plugins = array ();
	$plugin_root = WP_PLUGIN_DIR;
	if ( !empty($plugin_folder) )
		$plugin_root .= $plugin_folder;

	// Files in wp-content/plugins directory
	$plugins_dir = @ opendir( $plugin_root);
	$plugin_files = array();
	if ( $plugins_dir ) {
		while (($file = readdir( $plugins_dir ) ) !== false ) {
			if ( substr($file, 0, 1) == '.' )
				continue;
			if ( is_dir( $plugin_root.'/'.$file ) ) {
				$plugins_subdir = @ opendir( $plugin_root.'/'.$file );
				if ( $plugins_subdir ) {
					while (($subfile = readdir( $plugins_subdir ) ) !== false ) {
						if ( substr($subfile, 0, 1) == '.' )
							continue;
						if ( substr($subfile, -4) == '.php' )
							$plugin_files[] = "$file/$subfile";
					}
				}
			} else {
				if ( substr($file, -4) == '.php' )
					$plugin_files[] = $file;
			}
		}
	} else {
		return $wp_plugins;
	}

	@closedir( $plugins_dir );
	@closedir( $plugins_subdir );

	if ( empty($plugin_files) )
		return $wp_plugins;

	foreach ( $plugin_files as $plugin_file ) {
		if ( !is_readable( "$plugin_root/$plugin_file" ) )
			continue;

		$plugin_data = get_plugin_data( "$plugin_root/$plugin_file", false, false ); //Do not apply markup/translate as it'll be cached.

		if ( empty ( $plugin_data['Name'] ) )
			continue;

		$wp_plugins[plugin_basename( $plugin_file )] = $plugin_data;
	}

	uasort( $wp_plugins, '_sort_uname_callback' );

	$cache_plugins[ $plugin_folder ] = $wp_plugins;
	cache::set('plugins', $cache_plugins, 'plugins');

	return $wp_plugins;
}

/**
 * Callback to sort array by a 'Name' key.
 *
 * @since 3.1.0
 * @access private
 */
function _sort_uname_callback( $a, $b ) {
	return strnatcasecmp( $a['Name'], $b['Name'] );
}



/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 2.5.0
 *
 * @param string $plugin Base plugin path from plugins directory.
 * @return bool True, if in the active plugins list. False, not in the list.
 */
function is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}

/**
 * Check whether the plugin is inactive.
 *
 * Reverse of is_plugin_active(). Used as a callback.
 *
 * @since 3.1.0
 * @see is_plugin_active()
 *
 * @param string $plugin Base plugin path from plugins directory.
 * @return bool True if inactive. False if active.
 */
function is_plugin_inactive( $plugin ) {
	return ! is_plugin_active( $plugin );
}



/**
 * Attempts activation of plugin in a "sandbox" and redirects on success.
 *
 * A plugin that is already activated will not attempt to be activated again.
 *
 * The way it works is by setting the redirection to the error before trying to
 * include the plugin file. If the plugin fails, then the redirection will not
 * be overwritten with the success message. Also, the options will not be
 * updated and the activation hook will not be called on plugin error.
 *
 * It should be noted that in no way the below code will actually prevent errors
 * within the file. The code should not be used elsewhere to replicate the
 * "sandbox", which uses redirection to work.
 * {@source 13 1}
 *
 * If any errors are found or text is outputted, then it will be captured to
 * ensure that the success redirection will update the error redirection.
 *
 * @since 2.5.0
 *
 * @param string $plugin Plugin path to main plugin file with plugin data.
 * @param string $redirect Optional. URL to redirect to.
 * @param bool $silent Prevent calling activation hooks. Optional, default is false.
 * @return WP_Error|null WP_Error on invalid file or null on success.
 */
function activate_plugin( $plugin, $redirect = '', $silent = false ) {
	$plugin = plugin_basename( trim( $plugin ) );

	$current = get_option( 'active_plugins', array() );


	$valid = validate_plugin($plugin);
	if ( is_wp_error($valid) )
		return $valid;

	if ( !in_array($plugin, $current) ) {
		if ( !empty($redirect) )
			wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect)); // we'll override this later if the plugin can be included without fatal error
		ob_start();
		include_once(WP_PLUGIN_DIR . '/' . $plugin);

		if ( ! $silent ) {
			do_action( 'activate_plugin', $plugin );
			do_action( 'activate_' . $plugin );
		}

		$current[] = $plugin;
		sort($current);
		update_option('active_plugins', $current);

		if ( ! $silent ) {
			do_action( 'activated_plugin', $plugin);
		}

		if ( ob_get_length() > 0 ) {
			$output = ob_get_clean();
			return new DC_Error('unexpected_output', 'The plugin generated unexpected output.', $output);
		}
		ob_end_clean();
	}

	return null;
}

/**
 * Deactivate a single plugin or multiple plugins.
 *
 * The deactivation hook is disabled by the plugin upgrader by using the $silent
 * parameter.
 *
 * @since 2.5.0
 *
 * @param string|array $plugins Single plugin or list of plugins to deactivate.
 * @param bool $silent Prevent calling deactivation hooks. Default is false.
 */
function deactivate_plugins( $plugins, $silent = false ) {
	if ( is_multisite() )
		$network_current = get_site_option( 'active_sitewide_plugins', array() );
	$current = get_option( 'active_plugins', array() );
	$do_blog = $do_network = false;

	foreach ( (array) $plugins as $plugin ) {
		$plugin = plugin_basename( trim( $plugin ) );
		if ( ! is_plugin_active($plugin) )
			continue;


		if ( ! $silent )
			do_action( 'deactivate_plugin', $plugin );


		$key = array_search( $plugin, $current );
		if ( false !== $key ) {
			$do_blog = true;
			array_splice( $current, $key, 1 );
		}

		if ( ! $silent ) {
			do_action( 'deactivate_' . $plugin );
			do_action( 'deactivated_plugin', $plugin );
		}
	}

	if ( $do_blog )
		update_option('active_plugins', $current);
}

/**
 * Activate multiple plugins.
 *
 * When WP_Error is returned, it does not mean that one of the plugins had
 * errors. It means that one or more of the plugins file path was invalid.
 *
 * The execution will be halted as soon as one of the plugins has an error.
 *
 * @since 2.6.0
 *
 * @param string|array $plugins
 * @param string $redirect Redirect to page after successful activation.
 * @param bool $silent Prevent calling activation hooks. Default is false.
 * @return bool|WP_Error True when finished or WP_Error if there were errors during a plugin activation.
 */
function activate_plugins( $plugins, $redirect = '', $silent = false ) {
	if ( !is_array($plugins) )
		$plugins = array($plugins);

	$errors = array();
	foreach ( $plugins as $plugin ) {
		if ( !empty($redirect) )
			$redirect = add_query_arg('plugin', $plugin, $redirect);
		$result = activate_plugin($plugin, $redirect, $silent);
		if ( is_wp_error($result) )
			$errors[$plugin] = $result;
	}

	if ( !empty($errors) )
		return new DC_Error('plugins_invalid', 'One of the plugins is invalid.', $errors);

	return true;
}




//
// Menu
//

/**
 * Add a top level menu page
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string $page_title The text to be displayed in the title tags of the page when the menu is selected
 * @param string $menu_title The text to be used for the menu
 * @param string $capability The capability required for this menu to be displayed to the user.
 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu)
 * @param callback $function The function to be called to output the content for this page.
 * @param string $icon_url The url to the icon to be used for this menu
 * @param int $position The position in the menu order this one should appear
 *
 * @return string The resulting page's hook_suffix
 */
function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = NULL ) {
	global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;

	$menu_slug = plugin_basename( $menu_slug );

	$admin_page_hooks[$menu_slug] = sanitize_title( $menu_title );

	$hookname = get_plugin_page_hookname( $menu_slug, '' );

	if ( !empty( $function ) && !empty( $hookname ) && current_user_can( $capability ) )
		add_action( $hookname, $function );

	if ( empty($icon_url) )
		$icon_url = esc_url( admin_url( 'images/generic.png' ) );
	elseif ( is_ssl() && 0 === strpos($icon_url, 'http://') )
		$icon_url = 'https://' . substr($icon_url, 7);

	$new_menu = array( $menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $hookname, $hookname, $icon_url );

	if ( null === $position  )
		$menu[] = $new_menu;
	else
		$menu[$position] = $new_menu;

	$_registered_pages[$hookname] = true;

	// No parent as top level
	$_parent_pages[$menu_slug] = false;

	return $hookname;
}



/**
 * Remove a top level admin menu
 *
 * @since 3.1.0
 *
 * @param string $menu_slug The slug of the menu
 * @return array|bool The removed menu on success, False if not found
 */
function remove_menu_page( $menu_slug ) {
	global $menu;

	foreach ( $menu as $i => $item ) {
		if ( $menu_slug == $item[2] ) {
			unset( $menu[$i] );
			return $item;
		}
	}

	return false;
}

/**
 * Remove an admin submenu
 *
 * @since 3.1.0
 *
 * @param string $menu_slug The slug for the parent menu
 * @param string $submenu_slug The slug of the submenu
 * @return array|bool The removed submenu on success, False if not found
 */
function remove_submenu_page( $menu_slug, $submenu_slug ) {
	global $submenu;

	if ( !isset( $submenu[$menu_slug] ) )
		return false;

	foreach ( $submenu[$menu_slug] as $i => $item ) {
		if ( $submenu_slug == $item[2] ) {
			unset( $submenu[$menu_slug][$i] );
			return $item;
		}
	}

	return false;
}

/**
 * Get the url to access a particular menu page based on the slug it was registered with.
 *
 * If the slug hasn't been registered properly no url will be returned
 *
 * @since 3.0
 *
 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu)
 * @param bool $echo Whether or not to echo the url - default is true
 * @return string the url
 */
function menu_page_url($menu_slug, $echo = true) {
	global $_parent_pages;

	if ( isset( $_parent_pages[$menu_slug] ) ) {
		$parent_slug = $_parent_pages[$menu_slug];
		if ( $parent_slug && ! isset( $_parent_pages[$parent_slug] ) ) {
			$url = admin_url( add_query_arg( 'page', $menu_slug, $parent_slug ) );
		} else {
			$url = admin_url( 'admin.php?page=' . $menu_slug );
		}
	} else {
		$url = '';
	}

	$url = esc_url($url);

	if ( $echo )
		echo $url;

	return $url;
}
