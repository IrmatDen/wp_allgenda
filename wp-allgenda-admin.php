<?php
    echo '<div class="wrap">';
    if (!function_exists('curl_version')) {
        echo '<p><strong>' . _e('Curl is NOT enabled the plugin won\'t work!', 'wp_allgenda_trdom' ) . '</strong></p>';
    }
    
    include('wp-allgenda-defaults.php');
    include('wp-allgenda-utils.php');
    
    // Form will redirect to itself upon submission. The hidden field is used
    // to disambiguate between POST/GET
    if($_POST['wp_allgenda_hidden'] == 'Y')
    {
        //Form data sent
        $gid = $_POST['wp_allgenda_gid'];
        update_option('wp_allgenda_gid', $gid);
         
        $noe = $_POST['wp_allgenda_noe'];
        update_option('wp_allgenda_noe', $noe);
         
        $timezone = $_POST['wp_allgenda_timezone'];
        update_option('wp_allgenda_timezone', $timezone);
         
        $caption = $_POST['wp_allgenda_widget_caption'];
        update_option('wp_allgenda_widget_caption', $caption);
    
        echo '<div class="updated"><p><strong>' . _e('Options saved.', 'wp_allgenda_trdom' ) . '</strong></p></div>';
    }
    else
    {
        //Normal page display
        $gid = get_option('wp_allgenda_gid');
        
        $noe = get_option('wp_allgenda_noe');
        if (!$noe)
            $noe = Allgenda_Def_Noe;
            
        $timezone = get_option('wp_allgenda_timezone');
        if (!$timezone)
            $timezone = get_option('timezone_string');
        
        $caption = get_option('wp_allgenda_widget_caption');
        if (!$caption)
            $caption = Allgenda_Def_Caption;
    }
    
    echo '<h2>' . __( 'Allgenda widget options', 'wp_allgenda_trdom' ) . '</h2>';
     
    $form_uri = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
    echo '<form name="wp_allgenda_form" method="post" action="' . $form_uri . '">';
    echo '<h4>' . __( 'Allgenda widget options', 'wp_allgenda_trdom' ) . '</h4>';
    
    // Group ID field
    echo '<p>' . _e('Group id: ', 'wp_allgenda_trdom' ) . '<input type="text" name="wp_allgenda_gid" value="' . $gid . '" size="20">'._e(" ex: 1234", 'wp_allgenda_trdom' ) . '</p>';
    
    // Number of upcoming events to retrieve
    echo '<p>' . _e('Number of events: ', 'wp_allgenda_trdom' ) . '<input type="text" name="wp_allgenda_noe" value="' . $noe . '" size="20">' . _e(" ex: 5", 'wp_allgenda_trdom' ) . '</p>';
    
    // Number of upcoming events to retrieve
    echo '<p>' . _e('Widget caption: ', 'wp_allgenda_trdom' ) . '<input type="text" name="wp_allgenda_widget_caption" value="' . $caption . '" size="20">' . _e(" ex: Our events", 'wp_allgenda_trdom' ) . '</p>';
    
    // Timezone used to map Allgenda timestamps to
    echo '<p>' . _e('Timezone: ', 'wp_allgenda_trdom' ) . '<select name="wp_allgenda_timezone">';
    $known_tzs = timezone_list();
    foreach($known_tzs as $id => $label) {
        echo '<option value="' . $id . '"';
        if ($id == $timezone) {
            echo ' selected="selected"';
        }
        echo '>' . $label . '</option>';
    }
    echo '</select></p>';
    
    
    echo '<input type="hidden" name="wp_allgenda_hidden" value="Y">';
    echo '<p class="submit">';
    echo '<input type="submit" name="Submit" value="' . translate('Update Options', 'wp_allgenda_trdom' ) . '" />';
    echo '</p>';
    echo '</form>';
    echo '</div>';
?>