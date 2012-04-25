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
 *
 * @return array See above for description.
 */
function get_plugin_data( $plugin_file, $markup = true) {
	$default_headers = array(
		'Name' => 'Name',
		'URI' => 'URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
	);

	$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

	//For backward compatibility by default Title is the same as Name.
	$plugin_data['Title'] = $plugin_data['Plugin Name'];

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
		if ( ! empty($plugin_data['URI']) && ! empty($plugin_data['Name']) )
			$plugin_data['Title'] = '<a href="' . $plugin_data['URI'] . '" title="' .  'Visit plugin homepage'  . '">' . $plugin_data['Name'] . '</a>';
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
 */
function get_plugin_files($plugin) {
	$plugin_file = DCP . $plugin;
	$dir = dirname($plugin_file);
	$plugin_files = array($plugin);
	if ( is_dir($dir) && $dir != DCP ) {
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
 * 检查某插件是否是激活状态
 */
function is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}



/**
 * 激活插件
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
 * 停用插件
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
 * 批量激活插件
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



/**
 * Gets the basename of a plugin. 相对路径
 */
function plugin_basename($file) {
	$file = str_replace('\\','/',$file); // sanitize for Win32 installs
	$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
	$file = str_replace(DCP, '', $file); // get relative path from plugins dir
	$file = trim($file, '/');
	return $file;
}

/**
 * 获取插件文件绝对路径 带/
 */
function plugin_dir_path( $file ) {
	return trailingslashit( dirname( $file ) );
}

/**
 * 获取插件文件URL路径 带/
 */
function plugin_dir_url( $file ) {
	return trailingslashit( plugins_url( '', $file ) );
}
