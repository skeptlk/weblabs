
<?php 

const Q_INSERT_LOG = "INSERT INTO server_log (ip, date, path, method, response_code, response_size, user_agent) VALUES ";
const Q_GET_STATUS = "SELECT status FROM log_status WHERE id = 1";
const CLF_REGEX = '/^(\S+) \S+ \S+ \[([^\]]+)\] "([^"]*)" ([^"]+) ([^"]+) "[^"]*" "([^"]*)"$/m';
const DB_BATCH_SIZE = 4000;

$mysqli = new mysqli('127.0.0.1', 'auth_user', 'drowssap', 'auth_demo');


if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    die();
}

