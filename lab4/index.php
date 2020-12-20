<?php

include("./db/connect.php");
include("./functions.php");
include("./controllers.php");

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = explode('/', $path);
if ($path[0] == "") $path[0] = "home";
$action = $path[0];

$routes = [
    'pages' => ["home", "myaccount", "login", "signup", "restore_pass", "restore"],
    'api' => [
        "api_signup", "api_login", "api_logout", 
        "api_activate", "api_edit_profile", 
        "api_request_pass_restore", 
        "api_restore_password",
    ]
];

page_header();

if (in_array($action, $routes["api"])) {
    $action($mysqli, $path);
}
elseif (in_array($action, $routes["pages"])) {
    controller($action, $mysqli);
}
else {
    view("404");
}

page_footer();

?>

