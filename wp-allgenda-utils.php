<?php
    class WPAllgendaWidget
    {
        private $content_box_opened = false;
        public $allgenda_img_url;
        
        // ctor & dtor will simply setup our widget box
        function __construct() {
            echo '<div class="allgenda_wrap">';
        }
        
        function begin_render() {
            echo '<table id="allgenda"><tr>';
            echo '<th style="width:52px"/>';
            echo '<th style="width:130px"/>';
            echo '<th style="width:200px"/>';
            echo '</tr>';
            $this->content_box_opened = true;
        }
        
        function render_error($err) {
            echo $err;
        }
        
        // $event is supposed to be a json object describing a single event from allgenda
        function render_event($event, $target_timezone) {
            $date = new DateTime();
            
            $tz = new DateTimeZone($target_timezone);
            
            $tmp = $event->{'startDate'};
            $tmp = substr($tmp, 0, 10);
            $date->setTimestamp($tmp);
            $date->setTimezone($tz);
            $evt_beg_date = $date->format('d/m/Y');
            $evt_beg_time = $date->format('H:i');
            
            $tmp = $event->{'endDate'};
            $tmp = substr($tmp, 0, 10);
            $date->setTimestamp($tmp);
            $date->setTimezone($tz);
            $evt_end_date = $date->format('d/m/Y');
            $evt_end_time = $date->format('H:i');
            
            $meeting_label = '';
            if ($evt_beg_date == $evt_end_date) {
                $meeting_label = $evt_beg_date . ' (' . $evt_beg_time . ' - ' . $evt_end_time . ')';
            }  else {
                $meeting_label = $evt_beg_date . ' ' . $evt_beg_time . '<br/>' . $evt_end_date . ' ' . $evt_end_time;
            }
            
            // We need to check if img is direct URL or an allgenda builtin icon
            // From http://stackoverflow.com/a/9623072
            $regex = "((https?|ftp)\:\/\/)?"; // SCHEME 
            $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass 
            $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP 
            $regex .= "(\:[0-9]{2,5})?"; // Port 
            $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path 
            $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query 
            $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor
            
            $img_path = $event->{'img'};
            if (!preg_match("/^$regex$/", $event->{'img'})) {
                $img_path = $this->allgenda_img_url . $img_path;
            }
            
            echo '<tr>';
            echo '<td><img height="48" width="48" src="' . $img_path . '"/></td>';
            echo '<td>' . $event->{'title'} . '</td>';
            echo '<td>' . $meeting_label . '</td>';
            echo '</tr>';
        }
        
        function close() {
            if ($this->content_box_opened) {
                echo '</table>';
            }
            echo '</div>';
        }
    }
    
    /*
     * @return a json object or an error string
     * @param string $gid (Allgenda Group ID)
     * @param string $noe (number of events to retrieve from allgenda)
     */
    function get_allgenda_info($gid, $noe) {
        $transient_key = 'wp-allgenda-cache-'.$gid.'-'.$noe;
        
        if (!($cached = get_transient($transient_key))) {
            $raw = get_content('http://dev.allgenda.com/?actionId=63&gid='.$gid.'&noe='.$noe);
            $json = json_decode($raw);
            
            $cached = NULL;
            if ($json != NULL) {
                set_transient($transient_key, $cached, 3 * MINUTE_IN_SECONDS);
                $cached = $json;
            } else {
                delete_transient($transient_key);
                $cached = $raw;
            }
        }
        
        return $cached;
    }
    
    /*
     * @return string
     * @param string $url
     * @desc Return string content from a remote file
     * @author Luiz Miguel Axcar (lmaxcar@yahoo.com.br)
     * NB: from php fopen discussion
     *     http://www.php.net/manual/fr/function.fopen.php#55922
     *     (with the addition of custom timeouts)
     */
    function get_content($url) {
        $offline_since = get_option('wp_allgenda_offline_since');
        if (!$offline_since) {
            $offline_since = 0;
            update_option('wp_allgenda_offline_since', $offline_since);
        }
        if ($offline_since > 0) {
            $curr_timestamp = getdate()[0];
            $diff_secs = $curr_timestamp - $offline_since;
            if ($diff_secs < 600)
                return __('Allgenda appears to be offline!', 'wp_allgenda_trdom');
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout in seconds
        
        ob_start();
        
        curl_exec ($ch);
        $err = curl_errno($ch);
        if ($err > 0) {
            $offline_since = getdate()[0];
            update_option('wp_allgenda_offline_since', $offline_since);
            
            if ($err == 28) {
                return __('Allgenda appears to be offline!', 'wp_allgenda_trdom');
            } else {
                return __('Allgenda error: ', 'wp_allgenda_trdom') . curl_error($ch);
            }
        }
        curl_close ($ch);
        $string = ob_get_contents();
        
        ob_end_clean();
        
        return $string;    
    }
    
    // Timezone list builder (from http://stackoverflow.com/a/21211073)
    function timezone_list() {
        static $timezones = null;
    
        if ($timezones === null) {
            $timezones = [];
            $offsets = [];
            $now = new DateTime();
    
            foreach (DateTimeZone::listIdentifiers() as $timezone) {
                $now->setTimezone(new DateTimeZone($timezone));
                $offsets[] = $offset = $now->getOffset();
                $timezones[$timezone] = '(' . format_GMT_offset($offset) . ') ' . format_timezone_name($timezone);
            }
    
            array_multisort($offsets, $timezones);
        }
    
        return $timezones;
    }
    
    function format_GMT_offset($offset) {
        $hours = intval($offset / 3600);
        $minutes = abs(intval($offset % 3600 / 60));
        return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
    }
    
    function format_timezone_name($name) {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('St ', 'St. ', $name);
        return $name;
    }
?>