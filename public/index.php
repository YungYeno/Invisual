<?php



$debug = true;

($debug) ? session_start() : error_reporting(0);

// -------------------------------------------------------------------------------

function getUserIpAddr () {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // Ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

if (!filter_var(getUserIpAddr(), FILTER_VALIDATE_IP)) {
    echo "incorrect ip address";
    die();
} else {
    $connected_ip = (string)getUserIpAddr();
}

// -------------------------------------------------------------------------------

$date = date("Y d m G i s", time());

$new_user = true;

$json_file = "../private/includes/user_data.json";

$obj = json_decode(file_get_contents($json_file, true));

foreach ($obj->recent_ips as $user) {
    if ($connected_ip == $user->ip) {
        $new_user = false;
        $user->last_seen = $date;
    }
}

if ($new_user) array_push($obj->recent_ips, (object) array("ip" => "$connected_ip", "last_seen" => $date));

file_put_contents($json_file, json_encode($obj));

// ---------------------------------------------------------------------------------

// $GLOBALS['uri'] = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
$GLOBALS['file_request'] = 'public/';
$GLOBALS['nav_request'] = './';

require "../private/includes/router.php";

$router = new router;

$routes = $router->get_routes();

// var_dump($routes);
if ($routes[0] == 'logout') {
    session_destroy();
    header("location: ./");
}

$controller = ($debug && isset($_SESSION['debug-ssid']) == 'ebb3b395-c8bf-41a9-bcac-7aed5017f5c8' || !$debug) ?
    "MainController" : "DebugController";

require "../private/controllers/" . $controller . ".php";

$controller = new $controller;

$controller->load_page($routes);