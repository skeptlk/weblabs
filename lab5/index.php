<?php

include("./db/connect.php");
include("./db/utils.php");
include("./handlers.php");
include("./functions.php");


$req = trim($_SERVER['REQUEST_URI'], '/');
$met = $_SERVER['REQUEST_METHOD'];

if (strpos($req, 'upload') !== false && $met == 'POST') {

    upload_file_handler($mysqli);

}
elseif (strpos($req, 'status') !== false && $met == 'GET') {

    status_handler($mysqli);

}
elseif (strpos($req, 'report') !== false && $met == 'GET') {

    report_handler($mysqli);

}
elseif (strpos($req, 'cancel') !== false && $met == 'GET') {

    cancel_hadler($mysqli);

}
else {

    echo file_get_contents("templates/home.html");

}

