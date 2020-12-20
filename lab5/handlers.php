<?php

function upload_file_handler($mysqli) {

    $file = file_get_contents($_POST['path']);
    $filesize = filesize($_POST['path']);
    $processed = 0; 
    set_status($mysqli, $processed, $filesize);

    $logs = explode(PHP_EOL, $file);

    $batch = [];

    foreach ($logs as $line) {
        try {
            $matches = [];
            $c = preg_match_all(CLF_REGEX, $line, $matches);

            if ($c && count($matches) >= 7) {
                $ip      = substr($matches[1][0], 0, 24);
                $time    = strtotime($matches[2][0]);
                $request = explode(" ", $matches[3][0]);
                $code    = intval($matches[4][0]);
                $size    = intval($matches[5][0]);
                $uagent  = $mysqli->escape_string($matches[6][0]);
            
                $method = substr($mysqli->escape_string($request[0]), 0, 9);
                $path   = $mysqli->escape_string($request[1]);

                $batch[] = "('$ip', FROM_UNIXTIME('$time'), '$path', '$method', '$code', '$size', '$uagent')";
            }

            $processed += strlen($line);

            if (count($batch) > DB_BATCH_SIZE) {
                insert_batch($batch, $mysqli);
                $batch = [];
                set_status($mysqli, $processed, $filesize);
            }

        } catch (Exception $e) { }
    }

    insert_batch($batch, $mysqli);

    echo ("Gotovo!");
}


function report_handler($mysqli) {
    $groupby = $mysqli->real_escape_string($_GET['groupby']);
    $sess = intval($_GET['session_minutes']);
    
    $query = get_report_query($groupby, $sess);
    $res = $mysqli->query($query);

    if ($res) {
        $report = [];
        while ($row = $res->fetch_assoc())
            $report[] = $row;

        echo json_encode($report);
    }
    else {
        http_response_code(500);
        echo "Internal server error";
    }
}

function status_handler($mysqli) {
    $res = $mysqli->query(Q_GET_STATUS);

    if ($res) {
        $row = $res->fetch_assoc();
        echo $row['status'];
    } else {
        http_response_code(500);
        echo "Internal server error";
    }
}


function home_handler($mysqli) {
    echo file_get_contents("templates/home.html");
}


