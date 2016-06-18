<?php
/*
Plugin Name: SlimFAQ
Plugin URI: https://wordpress.org/plugins/slimfaq
Description: Integrate the <a href="https://slimfaq.com">SlimFAQ</a> sidebar into your WordPress website.
Author: Constantin Hofstetter (OOZOU)
Author URI: https://oozou.com
Version: 1.1.1



  -----------
  description
  -----------

  This plugin helps you integrate your FAQ from SlimFAQ into your WordPress page. Get started for free on slimfaq.com.

  -------
  license
  -------

  This is a plugin for WordPress (http://wordpress.org).

  Copyright Constantin Hofstetter (constantin@oozou.com), strongly influenced by Simon Blackbourn's (simon@lumpylemon.co.uk) Intercom for WordPress plugin (https://wordpress.org/plugins/intercom-for-wordpress)

  Released under the GPL license: http://www.opensource.org/licenses/gpl-license.php

  This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.


*/



defined( 'ABSPATH' ) or die();



define( 'LL_SLIMFAQ_VERSION', '1.1' );



$ll_slimfaq = new ll_slimfaq;



class ll_slimfaq {



  /**
   * class constructor
   * register the activation and de-activation hooks and hook into a bunch of actions
   */
  function __construct() {

    add_action( 'wp_footer',             array( $this, 'output_install_code'       ) );
    add_action( 'admin_menu',            array( $this, 'create_options_page'       ) );
    add_action( 'network_admin_menu',    array( $this, 'create_options_page'       ) );
    add_action( 'admin_init',            array( $this, 'settings_init'             ) );
    add_action( 'admin_notices',         array( $this, 'notice'                    ) );
    add_action( 'network_admin_notices', array( $this, 'notice'                    ) );

  }

  /**
   * check if this plugin is activated network-wide
   * @return boolean
   */
  function is_network_active() {

    if ( ! function_exists( 'is_plugin_active_for_network' ) )
      require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

    if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
      return true;

    return false;

  }



  /**
   * retrieve the slimfaq options
   * @return array 'll-slimfaq' options
   */
  function get_settings() {

    if ( $this->is_network_active() )
      return get_site_option( 'll-slimfaq' );

    return get_option( 'll-slimfaq' );

  }



  /**
   * update the slimfaq options in the database
   * @param  array $opts new options settings to save
   * @return null
   */
  function update_settings( $opts ) {

    if ( is_network_admin() ) {
      update_site_option( 'll-slimfaq', $opts );
    } else {
      update_option( 'll-slimfaq', $opts );
    }

  }



  /**
   * construct and output the intercom javascript snippet
   * @return null
   */
  function output_install_code() {

    // retrieve the options
    $opts = $this->get_settings();

    // don't do anything if:
    // the current user is hidden from slimfaq
    // or is not logged in and show-for-logged-out is disabled
    // or the ll_slimfaq_output_snippet filter returns false

    if ((!$opts['show-for-logged-out'] and !is_user_logged_in()) or !apply_filters( 'll_slimfaq_output_snippet', true ) )
      return;

    // don't do anything if the app id and secret key fields have not been set

    if ( !isset( $opts[ 'faq-id' ] ) or !$opts[ 'faq-id' ] )
      return;

    // generate and output the javascript

    $out  = '<script>';
    $out .= '  !function(a,b,c,d,e,f){e=b.createElement("script"),e.type="text/javascript",e.async=!0,e.src="https://cdn.slimfaq.com/widget/widget.js",f=b.getElementsByTagName("script")[0],f.parentNode.insertBefore(e,f),e.onload=function(){Slimfaq.init(c,d)}}';

    if ( $opts['intercom-enabled'] ) {
      $out .= '  (window,document,"' . $opts[ 'faq-id' ] . '",{intercom:"enable",button:"Contact Support"});';
    } else {
      $out .= '  (window,document,"' . $opts[ 'faq-id' ] . '",{button:"Contact Support"});';
    }

    $out .= '</script>';
    $out .= '<noscript>';
    $out .= '  <a href="https://slimfaq.com/'. $opts[ 'faq-id' ] .'" target="_blank">FAQ on SlimFAQ</a>';
    $out .= '</noscript>';

    echo $out;
  }



  /**
   * show a 'settings saved' notice
   * and a friendly reminder if the SlimFAQ ID haven't been entered
   * @return null
   */
  function notice() {

    if ( isset( $_GET[ 'page' ] ) and ( 'slimfaq' == $_GET[ 'page' ] ) ) {

      if ( is_network_admin() and isset( $_GET[ 'updated' ] ) ) { ?>
        <div class="updated" id="ll-slimfaq-updated"><p><?php _e( 'Settings saved.' ); ?></p></div>
        <?php
      }

    }

    // show an slimfaq reminder to users who can update options

    if ( ! current_user_can( 'manage_options' ) )
      return;

    $opts = $this->get_settings();

    if ( !is_network_admin() and ( !isset( $opts[ 'faq-id' ] ) or !$opts[ 'faq-id' ] ) ) {
      echo '<div class="error" id="ll-slimfaq-notice"><p><strong>SlimFAQ needs some attention</strong>. ';
      if ( isset( $_GET[ 'page' ] ) and 'slimfaq' == $_GET[ 'page' ] ) {
        echo 'Please enter your SlimFAQ ID';
      } else {
        echo 'Please <a href="options-general.php?page=slimfaq">configure the SlimFAQ settings</a>';
      }
      echo ' to integrate your FAQ.</p></div>' . "\n";
    }

  }



  /**
   * create the relevant type of slimfaq options page
   * depending if we're single site or network active
   * @return null
   */
  function create_options_page() {

    // annoyingly multisite doesn't play nicely with the settings api
    // so we need to account for that by creating a special page

    if ( $this->is_network_active() ) {

      add_submenu_page(
        'settings.php',
        'SlimFAQ Settings',
        'SlimFAQ',
        'manage_network_options',
        'slimfaq',
        array( $this, 'render_options_page' )
        );

    } else {

      add_options_page(
        'SlimFAQ Settings',
        'SlimFAQ',
        'manage_options',
        'slimfaq',
        array( $this, 'render_options_page' )
        );

    }

  }



  /**
   * output the SlimFAQ options page
   * @return null
   */
  function render_options_page() {

    $opts = $this->get_settings();

    $action = is_network_admin() ? 'settings.php?page=slimfaq' : 'options.php';

    ?>

    <div class="wrap">

    <?php screen_icon( 'options-general' ); ?>
    <h2>SlimFAQ Configuration</h2>

    <div class="postbox-container" style="width:65%;">

      <form method="post" action="<?php echo $action; ?>">

        <?php settings_fields( 'slimfaq' ); ?>

        <table class="form-table">
          <tbody>

            <tr valign="top">
              <th scope="row">FAQ ID</th>
              <td>
                <input name="ll-slimfaq[faq-id]" type="text" value="<?php echo esc_attr( $opts[ 'faq-id' ] ); ?>">
                <p class="description" id="ll-slimfaq-faq-id-description">Please get this value from your <a href="https://slimfaq.com/account/settings">SlimFAQ settings page</a>.</p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">Show for logged out users?</th>
              <td>
                <input name="ll-slimfaq[show-for-logged-out]" type="checkbox" value="1" <?php checked( $opts[ 'show-for-logged-out' ] ); ?>>
                <p class="description" id="ll-slimfaq-show-for-logged-out-description">Enable this setting to display the FAQ as a sidebar for visitors without an account.</p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">Integrate with Intercom?</th>
              <td>
                <input name="ll-slimfaq[intercom-enabled]" type="checkbox" value="1" <?php checked( $opts[ 'intercom-enabled' ] ); ?>>
                <p class="description" id="ll-slimfaq-intercom-enabled">Enable this setting if you have <a href="https://intercom.io">Intercom</a> installed.</p>
              </td>
            </tr>
          </tbody>

        </table>

        <p class="submit">
          <input class="button-primary" name="ll-slimfaq-submit" type="submit" value="Save Settings">
        </p>

      </form>

    </div>

    <div class="postbox-container" style="width:20%;">

      <div class="metabox-holder">

        <div class="meta-box-sortables" style="min-height:0;">
          <div class="postbox ll-slimfaq-info" id="ll-slimfaq-support">
            <h3 class="hndle"><span>Need Help?</span></h3>
            <div class="inside">
              <p>Please see our <a href="https://slimfaq.com/faq/23-integration">FAQ on integrations</a> if you have any questions.</p>
              <p>Feel free to <a href="https://slimfaq.com">get in touch</a> if you need help integrating your FAQ.</p>
            </div>
          </div>
        </div>

        <div class="meta-box-sortables" style="min-height:0;">
          <div class="postbox ll-slimfaq-info" id="ll-slimfaq-suggest">
            <h3 class="hndle"><span>Like this Plugin?</span></h3>
            <div class="inside">
              <p>If this plugin has helped you improve your customer relationships, please consider supporting it:</p>
              <ul>
                <li><a href="https://wordpress.org/extend/plugins/slimfaq/">Rate it and let other people know it works</a>.</li>
                <li>Link to it or share it on Twitter or Facebook.</li>
                <li>Write a review on your website or blog.</li>
              </ul>
            </div>
          </div>
        </div>

      </div>

    </div>
    </div>
    <?php

  }



  /**
   * use the WordPress settings api to initiate the various intercom settings
   * and if it's a network settings page then validate & update any submitted settings
   * @return null
   */
  function settings_init() {

    register_setting( 'slimfaq', 'll-slimfaq', array( $this, 'validate' ) );
    if ( isset( $_REQUEST[ '_wpnonce' ] ) and wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'slimfaq-options' ) ) {

      $file = is_network_admin() ? 'settings.php' : 'options-general.php';

      if ( isset( $_POST[ 'll-slimfaq-submit' ] ) and is_network_admin() ) {
        $opts = $this->validate( $_POST[ 'll-slimfaq' ] );
        $this->update_settings( $opts );
        wp_redirect( add_query_arg( array(
          'page'    => 'slimfaq',
          'updated' => true
          ), $file ) );
        die();
      }

    }

  }



  /**
   * make sure that no dodgy stuff is trying to sneak into the intercom settings
   * @param  array $input options to validate
   * @return array        validated options
   */
  function validate( $input ) {

    $new[ 'faq-id' ]              = wp_kses( trim( $input[ 'faq-id' ] ), array() );
    $new[ 'show-for-logged-out' ] = absint( $input[ 'show-for-logged-out' ] );
    $new[ 'intercom-enabled' ]    = absint( $input[ 'intercom-enabled' ] );

    return $new;

  }



} // class
