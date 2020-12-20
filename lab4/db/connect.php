
<?php 

// Enable us to use Headers
ob_start();

// Set sessions
if(!isset($_SESSION)) {
    session_start();
}

$mysqli = new mysqli('127.0.0.1', 'auth_user', 'drowssap', 'auth_demo');

if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    exit;
}
