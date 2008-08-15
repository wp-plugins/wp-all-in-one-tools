<?php
/*
Plugin Name: WP All-in-One tools
Plugin URI: http://www.infine.ru/support/wp_plugins/wp-all-in-one-tools.htm
Description: Collection of more usefull plugins and functions for WordPress (secure, feed, patches etc.)
Author: Nikolay aka 'cmepthuk'
Version: 0.1b
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
                     'disable-core-update' =>array(
                                             uniqId => 'disable-core-update'
                                             ),
                     'disable-plugin-update'=>array(
                                             uniqId => 'disable-plugin-update'
                                             ),
                     'show-me-options'    => array(
                                             cell_class => 'ShowMeOptions',
                                             uniqId => 'show-me-options'
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
    h2 > small {
      font-size: 12px;
      padding-left: 28px;
      background: #fff url('<?php print(AIO_IMAGES_URL); ?>home.png') 14px center no-repeat;
    }
    h2 > form {
      display: inline;
    }
    h2 > form > input {
      display: inline;
    }
    .aio_section_head {
      background-color: #464646;
      color: #aaa;
      text-align: right;
      position: relative;
      width: 600px;
      left: -400px;
      padding: 6px;
      margin: 10px 0 9px 0;
    }
    .aio_on, .aio_off {
      padding: 3px 0 0 3px !important;
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

<form method="post" action="">
  <h2>
    <?php $plugin_form = get_plugin_form_info('replace-wp-version', $active_plugins); ?>
    <input type="hidden" name="plugin_name" value="<?php print($plugin_form[name]); ?>" />
    <input type="hidden" name="plugin_full_name" value="Replace WP-Version" />
    <input type="hidden" name="act" value="<?php print($plugin_form[act]); ?>" />
    <input type="image"  id="<?php print($plugin_form[name]); ?>" class="<?php print($plugin_form[cssclass]); ?>" src="<?php print($plugin_form[image_url]); ?>" value="" title="<?php print($plugin_form[btn_title]); ?>" />
    <label for="<?php print($plugin_form[name]); ?>">Replace WP-Version</label>
    <small><a href="http://bueltge.de/wordpress-version-verschleiern-plugin/602/">Plugin home page</a></small>
  </h2>
  <p>Replace the WP-version with a random string &lt; WP 2.4 and eliminate WP-version &gt; WP 2.4</p>
</form>

<p>&nbsp;</p>

<div class="aio_section_head">WordPress Optimization:</div>

<form method="post" action="">
  <h2>
    <?php $plugin_form = get_plugin_form_info('disable-core-update', $active_plugins); ?>
    <input type="hidden" name="plugin_name" value="<?php print($plugin_form[name]); ?>" />
    <input type="hidden" name="plugin_full_name" value="Disable WordPress Core Update" />
    <input type="hidden" name="act" value="<?php print($plugin_form[act]); ?>" />
    <input type="image"  id="<?php print($plugin_form[name]); ?>" class="<?php print($plugin_form[cssclass]); ?>" src="<?php print($plugin_form[image_url]); ?>" value="" title="<?php print($plugin_form[btn_title]); ?>" />
    <label for="<?php print($plugin_form[name]); ?>">Disable WordPress Core Update</label>
    <small><a href="http://lud.icro.us/disable-core-update/">Plugin home page</a></small>
  </h2>
  <p>Disables the WordPress core update checking and notification system.</p>
</form>

<form method="post" action="">
  <h2>
    <?php $plugin_form = get_plugin_form_info('disable-plugin-update', $active_plugins); ?>
    <input type="hidden" name="plugin_name" value="<?php print($plugin_form[name]); ?>" />
    <input type="hidden" name="plugin_full_name" value="Disable WordPress Plugin Updates" />
    <input type="hidden" name="act" value="<?php print($plugin_form[act]); ?>" />
    <input type="image"  id="<?php print($plugin_form[name]); ?>" class="<?php print($plugin_form[cssclass]); ?>" src="<?php print($plugin_form[image_url]); ?>" value="" title="<?php print($plugin_form[btn_title]); ?>" />
    <label for="<?php print($plugin_form[name]); ?>">Disable WordPress Plugin Updates</label>
    <small><a href="http://lud.icro.us/disable-wordpress-plugin-updates/">Plugin home page</a></small>
  </h2>
  <p>Disables the plugin update checking and notification system.</p>
</form>

<p>&nbsp;</p>

<div class="aio_section_head">WordPress Usability:</div>

<form method="post" action="">
  <h2>
    <?php $plugin_form = get_plugin_form_info('show-me-options', $active_plugins); ?>
    <input type="hidden" name="plugin_name" value="<?php print($plugin_form[name]); ?>" />
    <input type="hidden" name="plugin_full_name" value="Show Me Options" />
    <input type="hidden" name="act" value="<?php print($plugin_form[act]); ?>" />
    <input type="image"  id="<?php print($plugin_form[name]); ?>" class="<?php print($plugin_form[cssclass]); ?>" src="<?php print($plugin_form[image_url]); ?>" value="" title="<?php print($plugin_form[btn_title]); ?>" />
    <label for="<?php print($plugin_form[name]); ?>">Show Me Options</label>
    <small><a href="http://www.prelovac.com/vladimir/wordpress-plugins/show-me-options">Plugin home page</a></small>
  </h2>
  <p>Allows you to quckly access plugin options upon activation.</p>
</form>
</div>

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

### Plugin Name: Replace WP-Version ############################################
### Author: Frank Bueltge ######################################################
### Version: 1.0 ###############################################################
if(!function_exists($aio_plugins['replace-wp-version'][cell_function])
   and
   in_array($aio_plugins['replace-wp-version'][uniqId], $aio_active_plugins)) {

  aio_load_plugin('replace-wp-version.php');

}
################################################################################
################################################################################





### Plugin Name: Disable WordPress Core Update #################################
### Author: John Blackbourn ####################################################
### Version: 1.1 ###############################################################
if(in_array($aio_plugins['disable-core-update'][uniqId], $aio_active_plugins)) {

add_action('init', create_function('$a', "remove_action('init', 'wp_version_check');"));
add_filter('pre_option_update_core', create_function('$a', "return null;"));

}
################################################################################
################################################################################






### Plugin Name: Disable WordPress Plugin Updates ##############################
### Author: John Blackbourn ####################################################
### Version: 1.2 ###############################################################
if(in_array($aio_plugins['disable-plugin-update'][uniqId], $aio_active_plugins)) {

add_action('admin_menu', create_function('$a', "remove_action('load-plugins.php', 'wp_update_plugins');") );
	# Why use the admin_menu hook? It's the only one available between the above hook being added and being applied
add_action('admin_init', create_function('$a', "remove_action('admin_init', 'wp_update_plugins');"), 2 );
add_action('init', create_function('$a', "remove_action('init', 'wp_update_plugins');"), 2 );
add_filter('pre_option_update_plugins', create_function('$a', "return null;"));

}
################################################################################
################################################################################





### Plugin Name: Show Me Options  ##############################################
### Author: Vladimir Prelovac ##################################################
### Version: 0.2 ###############################################################
if(!class_exists($aio_plugins['show-me-options'][cell_class])
   and
   in_array($aio_plugins['show-me-options'][uniqId], $aio_active_plugins)) {

  aio_load_plugin('show-me-options.php');

}
################################################################################
################################################################################

?>