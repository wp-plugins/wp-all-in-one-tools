<?php
/*
Plugin Name: WP All-in-One tools
Plugin URI: http://www.infine.ru/support/wp_plugins/wp-all-in-one-tools.htm
Description: Collection of more usefull plugins and functions for WordPress (secure, feed, patches etc.)
Author: Nikolay aka 'cmepthuk'
Version: 0.2
License: GPL
Author URI: http://infine.ru/
*/

### Use WordPress 2.6 Constants
if (!defined('WP_CONTENT_DIR')) {
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}

define('AIO_VERSION', '0.2');

### Detect and store right slash, and declare path to plugin dir and filename
$aio_slash = eregi('WIN',PHP_OS) ? '\\' : '/';
$break = explode($aio_slash, __FILE__);
define('AIO_FOLDER_NAME', $break[count($break) - 2]);
define('AIO_FILE_NAME',   $break[count($break) - 1]);

### Declare pathes
define('AIO_FOLDER_DIR', WP_PLUGIN_DIR.'/'.AIO_FOLDER_NAME);
define('AIO_FOLDER_URL', WP_PLUGIN_URL.'/'.AIO_FOLDER_NAME);
define('AIO_IMAGES_URL', WP_PLUGIN_URL.'/'.AIO_FOLDER_NAME.'/images/admin/');
define('AIO_PLUGINS_DIR',AIO_FOLDER_DIR.'/plugins/');

### Declare option name
define('AIO_OPTIONS', 'aio-active-plugins');

define('AIO_PLIGINS_BR', '|');

### Global arrays
$aio_messages = array();
$aio_act = array(activate =>   array(act => 'activate',   hint => 'Activate plugin'),
                 deactivate => array(act => 'deactivate', hint => 'Deactivate plugin'));
$aio_plugins = array('replace-wp-version' => array(
                                             cell_function => 'fb_replace_wp_version',
                                             uniqId => 'replace-wp-version'
                                             ),
                     'iodized-salt' =>       array(
                                             cell_function => 'Iodized_Salt',
                                             uniqId => 'iodized-salt'
                                             ),
                     'disable-core-update' =>array(
                                             uniqId => 'disable-core-update'
                                             ),
                     'disable-plugin-update'=>array(
                                             uniqId => 'disable-plugin-update'
                                             ),
                     'allow-numeric-stubs' =>array(
                                             cell_function => 'allow_numeric_stubs',
                                             uniqId => 'allow-numeric-stubs'
                                             ),
                     'show-me-options'    => array(
                                             cell_class => 'ShowMeOptions',
                                             uniqId => 'show-me-options'
                                             ),
                     'minimum-comment-length' => array(
                                             cell_function => 'check_comment_length',
                                             uniqId => 'minimum-comment-length'
                                             )
                    );

function aio_get_active_plugins() {
  return array_unique(explode(AIO_PLIGINS_BR, get_option(AIO_OPTIONS)));
}

### Initialization function
function aio_init() {
  global $aio_messages;
  if(get_option(AIO_OPTIONS) == '') {
    # add default value
    add_option('aio-active-plugins', '', 'Option for plugin WP All-in-One tools', 'yes');
    array_push($aio_messages, 'Plugin initialization complete');
  }
}

### Function get array, and return string of array units, ex: val1|val2|val3
function plugins_list_to_str($plugins = array()) {
  static $string = '', $i = 0;
  if(!empty($plugins)) {
    //array_push($plugins, 'qwe'); array_push($plugins, 'wer'); array_push($plugins, 'tre');
    foreach($plugins as $plugin) {
      if($plugin !== '') {
      ### Add break char only in middle
        if(count($plugins) == 1) {
          $string .= $plugin;
        } else {
          $string .= ($i !== count($plugins) - 1) ? $plugin.AIO_PLIGINS_BR : $plugin;
        } $i++;
      }

    }
    return $string;
  }
}

### Function use POST values, and update option in needed
function aio_update_option() {
  global $aio_act, $aio_messages;
  static $active_plugins = array();
  $active_plugins = aio_get_active_plugins();
  if(isset($_POST['act'])){
    if($_POST['act'] == $aio_act[activate][act]) {
      if(!in_array($_POST['plugin_name'], $active_plugins)) {
        ### Store new activated plugin
        array_push($active_plugins, $_POST['plugin_name']);
        array_push($aio_messages, 'Plugin <strong>'.$_POST['plugin_full_name'].'</strong> activated');
      }
    } elseif($_POST['act'] == $aio_act[deactivate][act])
      if(in_array($_POST['plugin_name'], $active_plugins)) {
        ### Find in activated plugins array index of deactivated (by POST value), and remove it
        unset($active_plugins[array_search($_POST['plugin_name'], $active_plugins)]);
        array_push($aio_messages, 'Plugin <strong>'.$_POST['plugin_full_name'].'</strong> deactivated');
      }
    update_option(AIO_OPTIONS, plugins_list_to_str($active_plugins));
  }
}

                            ####################
                                 aio_init();     ### Init plugin, if need - write default value
                            aio_update_option(); ### Store values if needed
                            ####################

################################################################################
### Admin page #################################################################
function aio_admin_header() {
  if(strpos($_SERVER['REQUEST_URI'], AIO_FILE_NAME)) { ?>
  <style type="text/css" media="all">
    /* by WP All-in-One tools plugin */
    h2.aio_pluginname {
      font-size: 19px !important;
      font-family: Tahoma, Verdana !important;
      color: #444 !important;
      margin: 0px !important;
      padding: 0px !important;
      border: 0px !important;
      /*font-weight: bold !important;*/
    }
    h2.aio_pluginname > small {
      font-size: 12px;
      font-weight: normal !important;
      padding-left: 28px;
      background: #fff url('<?php print(AIO_IMAGES_URL); ?>home.png') 14px center no-repeat;
    }
    form > p.descr, div.descr, div.opt {
      padding: 0 0 0 25px;
      margin: 1px 0 15px 0;
    }
    form > p.descr {
      color: #666;
    }
    .aio_section_head {
      background-color: #e4f2fd;
      border: #c6d9e9 solid 1px;
      color: #464646;
      text-align: right;
      position: relative;
      width: 600px;
      left: -400px;
      padding: 6px;
      margin: 10px 0 15px 0;
    }
    .aio_footer {
      text-align: center;
      font-size: 9px;
      color: #c3c3c3;
    }
    .aio_footer a {
      color: #aaa;
    }

    .aio_on, .aio_off {
      padding: 3px 0 0 3px !important;
      margin: 0px;
    }
    .aio_table {
      width: 100%;
    }
    .aio_table td {
      background-color: #eaf3fa;
      padding: 5px;
      border: 2px solid #eff3fa;
    }
    .aio_table * > label {
      font-weight: bold;
    }
    .aio_table * > input {
      border: 1px solid #c6d9e9;
    }
    .aio_submit {
      font-size: 0.9em;
      background-color: #e5e5e5;
      padding: 5px;
      color: #246;
      border: 1px solid #80b5d0;
      margin: 10px 0 10px 0 !important;
    }
    .aio_submit:hover {
      color: #d54e21;
      cursor: pointer;
      border-color: #535353;
    }
  </style>
<?php }}
add_action('admin_head', 'aio_admin_header');

function aio_admin_page() {
  function get_plugin_form_info($plugin_name, $plugins_list) {
    global $aio_plugins, $aio_act;
    static $act_class = array(on => 'aio_on', off => 'aio_off'),
           $result = array(active => false,
                           name => '',
                           act => '',
                           css_class => '',
                           btn_title => '');
    $result[active] =    in_array($plugin_name, $plugins_list);
    $result[name] =      $aio_plugins[$plugin_name][uniqId];
    $result[act] =       $result[active] ? $aio_act[deactivate][act] : $aio_act[activate][act];
    $result[cssclass] =  $result[active] ? $act_class[on] : $act_class[off];
    $result[image_url] = $result[active] ? AIO_IMAGES_URL.'power_on.png' : AIO_IMAGES_URL.'power_off.png';
    $result[btn_title] = $result[active] ? $aio_act[deactivate][hint] : $aio_act[activate][hint];
    return $result;
  }

  function print_plugin_form($plugin = array(uniqId, full_name, uri => 'http://wordpress.org/extend/plugins/', description, close_form => true, options_div => false), $active_plugins) {
    static $plugin_form;
    $plugin_form = get_plugin_form_info($plugin[uniqId], $active_plugins);
    print("
<form method=\"post\" action=\"\">
  <h2 class=\"aio_pluginname\">
    <input type=\"hidden\" name=\"plugin_name\" value=\"".$plugin_form[name]."\" />
    <input type=\"hidden\" name=\"plugin_full_name\" value=\"".$plugin[full_name]."\" />
    <input type=\"hidden\" name=\"act\" value=\"".$plugin_form[act]."\" />
    <input type=\"image\"  id=\"".$plugin_form[name]."\" class=\"".$plugin_form[cssclass]."\" src=\"".$plugin_form[image_url]."\" value=\"\" title=\"".$plugin_form[btn_title]."\" />
    <label for=\"".$plugin_form[name]."\">".$plugin[full_name]."</label>
    <small><a href=\"".$plugin[uri]."\">Plugin home page</a></small>
  </h2>
  <p class=\"descr\">".$plugin[description]."</p>");
    if($plugin[close_form]) print("\n</form>\n");
    if($plugin[options_div])
      $plugin_form[active] ? print("<div class=\"opt\">\n") : print("<div style=\"display: none;\" class=\"opt\">\n");
  }
  global $aio_plugins, $aio_messages, $aio_act;
  static $active_plugins = array(),
         $plugin_form = array();

  $active_plugins = aio_get_active_plugins(); ### Get active plugins

  ### Show strings from $aio_messages array
  if(!empty($aio_messages)) {
    print("<div class=\"updated fade\">");
    foreach($aio_messages as $message)
      print("<p>".$message."</p>");
    print("</div>");
  }

  /*print_r($active_plugins);
  print '<hr />'.plugins_list_to_str($active_plugins);*/

?>

<div class="wrap">

<div class="aio_section_head">Security tools:</div>

<?php
  print_plugin_form(array(uniqId => 'replace-wp-version',
                          full_name => 'Replace WP-Version',
                          uri => 'http://bueltge.de/wordpress-version-verschleiern-plugin/602/',
                          description => 'Replace the WP-version with a random string &lt; WP 2.4 and eliminate WP-version &gt; WP 2.4',
                          close_form => true), $active_plugins);

  print_plugin_form(array(uniqId => 'iodized-salt',
                          full_name => 'Iodized Salt',
                          uri => 'http://blog.portal.khakrov.ua/',
                          description => 'Йодированная соль в печенье - примешивание IP клиента к хешам куков. Продотвращает использование ворованных куков с другого IP-адреса.',
                          close_form => true), $active_plugins);
?>

<p>&nbsp;</p>

<div class="aio_section_head">WordPress Optimization:</div>

<?php
  print_plugin_form(array(uniqId => 'disable-core-update',
                          full_name => 'Disable WordPress Core Update',
                          uri => 'http://lud.icro.us/disable-core-update/',
                          description => 'Disables the WordPress core update checking and notification system.',
                          close_form => true), $active_plugins);

  print_plugin_form(array(uniqId => 'disable-plugin-update',
                          full_name => 'Disable WordPress Plugin Updates',
                          uri => 'http://lud.icro.us/disable-wordpress-plugin-updates/',
                          description => 'Disables the plugin update checking and notification system.',
                          close_form => true), $active_plugins);
?>

<p>&nbsp;</p>

<div class="aio_section_head">WordPress Patches:</div>

<?php
  print_plugin_form(array(uniqId => 'allow-numeric-stubs',
                          full_name => 'Allow Numeric Stubs',
                          uri => 'http://www.viper007bond.com/wordpress-plugins/allow-numeric-stubs/',
                          description => 'Allows children Pages to have a stub that is only a number. Sacrifices the <code>&lt;!--nextpage--&gt;</code> ability in Pages to accomplish it.',
                          close_form => true), $active_plugins);
?>

<p>&nbsp;</p>

<div class="aio_section_head">WordPress Usability:</div>

<?php
  print_plugin_form(array(uniqId => 'show-me-options',
                          full_name => 'Show Me Options',
                          uri => 'http://www.prelovac.com/vladimir/wordpress-plugins/show-me-options',
                          description => 'Allows you to quckly access plugin options upon activation.',
                          close_form => true), $active_plugins);
?>

<p>&nbsp;</p>

<div class="aio_section_head">Comments&Posts:</div>

<?php
  print_plugin_form(array(uniqId => 'minimum-comment-length',
                          full_name => 'Minimum Comment Length',
                          uri => 'http://yoast.com/wordpress/minimum-comment-length/',
                          description => 'Check the comment for a set minimum length and disapprove it if it\'s too short (work with cyrillic alphabet with errors).',
                          close_form => true,
                          options_div => true), $active_plugins);
?>
<?php
  ### Author: Joost de Valk ########################################## begin ###
	$options['mincomlength'] = 15;
	$options['mincomlengtherror'] = "Error: Your comment is too short. Please try to say something useful.";
	add_option('MinComLengthOptions', $options);
	// Overwrite defaults with saved settings
	if ( isset($_POST['mincomlength-submit']) ) {
		/*if (!current_user_can('manage_options')) die(__('You cannot edit the Minimum Comment Length options.'));
		  check_admin_referer('mincomlength-config');*/
		if (isset($_POST['mincomlength']) && $_POST['mincomlength'] != "" && is_numeric($options['mincomlength']))
			$options['mincomlength'] = $_POST['mincomlength'];
		if (isset($_POST['mincomlengtherror']) && $_POST['mincomlengtherror'] != "")
			$options['mincomlengtherror'] = $_POST['mincomlengtherror'];
		update_option('MinComLengthOptions', $options);
    $mincom_updated = true; # added by Nikolay
	}
	$options = get_option('MinComLengthOptions');
  ###################################################################### end ###

?>
  <form action="" method="post" id="mincomlength-conf">
    <table class="aio_table">
      <tr>
        <td>
          <label for="mincomlength">Minimum comment length:</label>
        </td><td>
          <label for="mincomlengtherror">Error message:</label>
        </td>
      </tr><tr>
        <td>
          <input type="text" value="<?php echo $options['mincomlength']; ?>" name="mincomlength" id="mincomlength" size="4" />
        </td><td>
          <input type="text" value="<?php echo $options['mincomlengtherror']; ?>" name="mincomlengtherror" id="mincomlengtherror" size="50" />
        </td>
      </tr>
    </table>
    <input type="submit" name="mincomlength-submit" class="aio_submit<?php if($mincom_updated) print(" updated fade"); ?>" value="Update Settings &raquo;" />
  </form>
</div> <?php /* div open tag wroted by function */?>
<p>&nbsp;</p>

</div>
<div class="aio_footer">Version <?php print AIO_VERSION; ?>, Created in <a href="http://www.infine.ru/support/">infine.ru</a> web develop studio</div>
</div>
<?php }

function aio_add_admin_page() {
  add_options_page('All in One tools Options', __('All in One tools', 'all_in_one_tools'), 8, __FILE__, 'aio_admin_page');
}
add_action('admin_menu', 'aio_add_admin_page');
################################################################################
################################################################################


$aio_active_plugins = aio_get_active_plugins();

### Functiona check exist file, and load them
function aio_load_plugin($filename) {
  if(file_exists(AIO_PLUGINS_DIR.$filename))
    include(AIO_PLUGINS_DIR.$filename);
}

### Plugin Name: Replace WP-Version ########################### Version: 1.0 ###
### Author: Frank Bueltge ######################################################
if(!function_exists($aio_plugins['replace-wp-version'][cell_function])
   and
   in_array($aio_plugins['replace-wp-version'][uniqId], $aio_active_plugins)) {

  aio_load_plugin('replace-wp-version.php');

}
################################################################################


### Plugin Name: Replace WP-Version ########################### Version: 1.0 ###
### Author: Frank Bueltge ######################################################
if(in_array($aio_plugins['iodized-salt'][uniqId], $aio_active_plugins)) {

  add_filter('salt','iodized_salt');
  function iodized_salt($key) {
  	return md5($key.$_SERVER["REMOTE_ADDR"]);
  }

}
################################################################################


### Plugin Name: Disable WordPress Core Update ################ Version: 1.1 ###
### Author: John Blackbourn ####################################################
if(in_array($aio_plugins['disable-core-update'][uniqId], $aio_active_plugins)) {

  add_action('init', create_function('$a', "remove_action('init', 'wp_version_check');"));
  add_filter('pre_option_update_core', create_function('$a', "return null;"));

}
################################################################################


### Plugin Name: Disable WordPress Plugin Updates ############# Version: 1.2 ###
### Author: John Blackbourn ####################################################
if(in_array($aio_plugins['disable-plugin-update'][uniqId], $aio_active_plugins)) {

  add_action('admin_menu', create_function('$a', "remove_action('load-plugins.php', 'wp_update_plugins');") );
  	# Why use the admin_menu hook? It's the only one available between the above hook being added and being applied
  add_action('admin_init', create_function('$a', "remove_action('admin_init', 'wp_update_plugins');"), 2 );
  add_action('init', create_function('$a', "remove_action('init', 'wp_update_plugins');"), 2 );
  add_filter('pre_option_update_plugins', create_function('$a', "return null;"));

}
################################################################################


### Plugin Name: Allow Numeric Stubs ######################## Version: 1.0.0 ###
### Author: Viper007Bond #######################################################
if(in_array($aio_plugins['allow-numeric-stubs'][uniqId], $aio_active_plugins)) {

  // Register plugin hooks
  register_activation_hook( __FILE__, 'allow_numeric_stubs_activate' );
  add_filter( 'page_rewrite_rules', 'allow_numeric_stubs' );


  // Force a flush of the rewrite rules when this plugin is activated
  function allow_numeric_stubs_activate() {
  	global $wp_rewrite;
  	$wp_rewrite->flush_rules();
  }


  // Remove the rule that "breaks" it and replace it with a non-paged version
  function allow_numeric_stubs( $rules ) {
  	unset( $rules['(.+?)(/[0-9]+)?/?$'] );
  	$rules['(.+?)?/?$'] = 'index.php?pagename=$matches[1]';
  	return $rules;
  }

}

### Plugin Name: Show Me Options ############################## Version: 0.2 ###
### Author: Vladimir Prelovac ##################################################
if(!class_exists($aio_plugins['show-me-options'][cell_class])
   and
   in_array($aio_plugins['show-me-options'][uniqId], $aio_active_plugins)) {

  aio_load_plugin('show-me-options.php');

}
################################################################################


### Plugin Name: SMinimum Comment Length ###################### Version: 0.5 ###
### Author: Joost de Valk ######################################################
if(!function_exists($aio_plugins['minimum-comment-length'][cell_function])
   and
   in_array($aio_plugins['minimum-comment-length'][uniqId], $aio_active_plugins)) {

  function check_comment_length($commentdata) {
  	$options = get_option('MinComLengthOptions');
  	if ($options['mincomlength'] == "")
  		$options['mincomlength'] = 15;

  	if ($options['mincomlengtherror'] == "")
  		$options['mincomlengtherror'] = "Error: Your comment is too short. Please try to say something useful.";

  	if (strlen($commentdata['comment_content']) < $options['mincomlength']) {
  		wp_die($options['mincomlengtherror']);
  	} else {
  		return $commentdata;
  	}
  }
  add_filter('preprocess_comment','check_comment_length');

}
################################################################################

?>