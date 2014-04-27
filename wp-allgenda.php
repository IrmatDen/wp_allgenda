<?php
    /*
    Plugin Name: Allgenda widget
    Plugin URI: https://github.com/IrmatDen/wp_allgenda
    Description: Plugin to display upcoming events in your Allgenda group
    Author: Denys Bulant
    Version: 0.1
    Author URI: N/A
    */
    
    /* Dev info:
     * wp-allgenda will register the following options:
     * - wp_allgenda_gid: Allgenda groupid
     * - wp_allgenda_noe: The number of upcoming events to retrieve from allgenda (default: 5)
     * - wp_allgenda_timezone: timezone to interpret Allgenda times (default: WordPress timezone)
     * - wp_allgenda_widget_caption: Caption for sidebar widget (default: Allgenda)
     * - wp_allgenda_offline_since: "hidden" option; used to tag Allgenda as offline and not
     *                              generate any new requests for the next 10 minutes (avoid
     *                              WordPress visitors having to wait curl timeout, 3s currently).
     *
     * A transient is used to cache allgenda query results for 3 minutes (see get_json_allgenda_info).
     * The cached request is proper to each group ID and number of events.
     *
     * i18n: displayed strings should be found in the wp_allgenda_trdom domain
     *
     * Dependancy: curl [mandatory]
     */
    
    add_action('admin_menu', 'wp_allgenda_admin_menu');
    wp_register_sidebar_widget(
        'wp_allgenda',        // your unique widget id
        'Allgenda',          // widget name
        'wp_allgenda_sidebar_widget',  // callback function
        array(                  // options
            'description' => ''
        )
    );
    
    function wp_allgenda_admin_menu() {
        add_options_page("Configure Allgenda widget", "Allgenda widget", "manage_options", "AllgendaWidget", "wp_allgenda_admin");
    }
    
    function wp_allgenda_admin() {
        include("wp-allgenda-admin.php");
    }
    
    function wp_allgenda_sidebar_widget($args) {
        include('wp-allgenda-defaults.php');
        include('wp-allgenda-utils.php');
        
        // Check first if we have an Allgenda group id. Bail out if not available
        $gid = get_option('wp_allgenda_gid');
        if (!$gid)
            return;
        
        $noe = get_option('wp_allgenda_noe');
        if (!$noe)
            $noe = Allgenda_Def_Noe;
            
        $target_tz = get_option('wp_allgenda_timezone');
        if (!$target_tz)
            $target_tz = get_option('timezone_string');
        
        $caption = get_option('wp_allgenda_widget_caption');
        if (!$caption)
            $caption = Allgenda_Def_Caption;
        
        extract($args);
        echo $before_widget;
        echo $before_title . $caption . $after_title;
            
        $widget = new WPAllgendaWidget();
        
        $content = get_allgenda_info($gid, $noe);
        
        if (gettype($content) == "string") {
            $widget->render_error($content);
        }
        else {
            if (array_key_exists('errors', $content)) {
                $widget->render_error(__('Allgenda returned an error!', 'wp_allgenda_trdom'));
            } else {
                $widget->begin_render();
                $widget->allgenda_img_url = $content->{'img_path'};
                
                $arrEvents = $content->{'events'};
                foreach ($arrEvents as $k => $event) {
                    $widget->render_event($event, $target_tz);
                }
            }
        }
        $widget->close();
        echo $after_widget;
    }
?>