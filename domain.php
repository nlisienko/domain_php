<?php
/**
 * Domain informer
 * @version 1.1
 * @author NL neor.digital
 */

echo "Domain informer \n\n";

$domain = isset($argv[1]) ? $argv[1] : '';

if (empty($domain)) {
    die("Usage:  php domain.php <DOMAIN.XXX> \n");
}

echo "domain:     ". $domain ."\n";

$ip = $host_server = $dns = $mail_service = $host_service = $url = $web_server = $server_apps = '';
$duration = $has_ssl = 0;

// ip
$ip = @gethostbyname($domain);
if ( $ip!=$domain ) {
    echo "ip:         ". $ip ."\n";

    // host
    $host_server = gethostbyaddr($ip);
    $host_server = ($host_server!=$ip) ? $host_server : '';
    echo "hostname:   ". $host_server ."\n";

    // detect hosting by Host domain
    // get 2 level homain
    $host_service = '';
    if ($host_server) {
        $arr = array_reverse(explode('.', $host_server));
        $host_service = $arr[1].'.'.$arr[0];
        echo 'hosting:    '.$host_service ."\n";
    } 

    // detect mail service by Mail domain
    $mail_service = '';
    getmxrr($domain, $mx_records, $mx_weight);
    if ($mx_records) {
        $mail_server = $mx_records[ array_search(min($mx_weight), $mx_weight) ]; // find main Mail server
        //print_r($mx_records);
        $arr = array_reverse(explode('.', $mail_server));
        if ( count($arr) > 1) {
            $mail_service = $arr[1].'.'.$arr[0];
        }
        echo 'mailserver: '. $mail_server .' -> service: '. $mail_service ."\n";
    } 

    // dns
    $dns_arr = @dns_get_record($domain);
    // get txt  which starting "/^v=spf1\s/"
    // select all foreach in one big Text:
    //   type  ip | mname | rname | target | txt
    if ( !empty($dns_arr) ) {
		sort($dns_arr);
        $dns = array();
        foreach($dns_arr as $dns_str) {
            $str = '';
            $str .= isset($dns_str['ip']) ? ' '.$dns_str['ip'] : '';
            $str .= isset($dns_str['mname']) ? ' '.$dns_str['mname'] : '';
            $str .= isset($dns_str['rname']) ? ' '.$dns_str['rname'] : '';
            $str .= isset($dns_str['target']) ? ' '.$dns_str['target'] : '';
            $str .= isset($dns_str['txt']) ? ' '.$dns_str['txt'] : '';
            $dns[] = $dns_str['type'] . $str;   
        }
        $dns = implode("\n  ", $dns);
        echo "\nDNS:  [". strlen($dns) ."]\n  ". $dns ."\n";
    } else {
        echo "Can't get DNS \n";
    }

    // fetch headers
	$protocol = 'http';
    $location = $protocol .'://'. $domain;
    $url = '';
    $redirect_url = [];    
    while ($location) {  // try before end host
        
        $time_start1 = microtime(true);
        
        // Fetch data
        $context = stream_context_create( [ $protocol => array( 'method' => 'HEAD' ) ] );    
        $headers = @get_headers($location, false, $context);

        $duration = round( microtime(true) - $time_start1, 2); // duration in sec
        $url = $location;
        $redirect_url[] = $location;
        $location = ''; // null

        // if Location get from new url
        if ( $headers ) {
            foreach ($headers as $i => $str) {
                $str = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $str); // delete Unicode

                if ( strlen(trim($str)) < 5 ) {
                    unset($headers[$i]);
                } else {
                    $headers[$i] = $str;
                }

                preg_match( '/^Location:\s*(.*)/i', $str, $matches ); // Redirect
                if ( isset($matches[1]) ) { 
                    $location = $matches[1];
					if ( substr($location, 0, 5)=='https' ) {
						$protocol = 'https';
					}
					if ( substr($location, 0, 4)!='http' ) {
						$slash = (substr($location, 0, 1)!='/') ? '/' : '';
						$location = $protocol .'://'. $domain . $slash . $location;
					}
				}
            }
        }
    }

    if ( count($redirect_url)>1 ) {
        echo "\nRedirect:  [". count($redirect_url) ."]  ". implode("  >  ", $redirect_url) ."\n";
    } else {
        echo "\nRedirect: no\n";
    }

    $ssl = 'no';
    if ( !empty($headers) ) {
        preg_match( '/^https:*(.*)/i', $url, $matches ); // url 
        if ( isset($matches[1]) ) 
            $ssl = 'yes';

        echo "\nURL:  ". $url ."  Duration: ". $duration ." sec   SSL: ". $ssl ."\n";

        $headers = implode("\n  ", $headers);
        echo "Headers:  [". strlen($headers) ."]\n  ". $headers ."\n";

    } else {
        echo "Empty headers ".$url."\n";
    }
    echo "\n";
} // check ip
else {
    die("Can't resolve ip \n\n");
}
