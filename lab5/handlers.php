<?php

function upload_file_handler($mysqli) {
    // session_start();
    // session_regenerate_id();

    // OPEN FILE
    $fname = $_POST['path'];
    if (!file_exists($fname)) {
        http_response_code(403); return;
    }
    $handler = @fopen($fname, "r");
    if (!$handler) {
        http_response_code(500); return;
    }

    $filesize = filesize($fname);
    $processed = 0; 
    set_status($processed, $filesize);

    $batch = [];

    while (!feof($handler)) {
        $line = fgets($handler, 4096);
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
            set_status($processed, $filesize);

            if (count($batch) > DB_BATCH_SIZE) {
                insert_batch($batch, $mysqli);
                $batch = [];
            }
            if ($_SESSION["should_stop"] === "true") {
                http_response_code(418); return;
            }
                
        } catch (Exception $e) { }
    }

    fclose($handler);
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
    session_start();
    echo $_SESSION["status"];
}

function cancel_hadler($_) {
    session_start();
    $_SESSION["should_stop"] = "true";
    session_write_close();
}

