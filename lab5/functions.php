<?php

function get_report_query($groupby, $session_minutes)
{
    $sess = 60 * $session_minutes;
    if ($groupby == "month") {
        $report = 
            "SELECT AVG(avg_size) AS avg_size, MONTH(FROM_UNIXTIME(timeslice)) AS month FROM" .
            "(SELECT ip, AVG(response_size) as avg_size, ROUND((CEILING(UNIX_TIMESTAMP(date) / $sess) * $sess)) AS timeslice " .
                "FROM server_log GROUP BY ip, timeslice) " .
            "as a GROUP BY month";
    } elseif ($groupby == "week") {
        $report =
            "SELECT AVG(avg_size) AS avg_size, WEEK(FROM_UNIXTIME(timeslice)) AS week FROM" .
            "(SELECT ip, AVG(response_size) as avg_size, ROUND((CEILING(UNIX_TIMESTAMP(date) / $sess) * $sess)) AS timeslice " .
                "FROM server_log GROUP BY ip, timeslice) " .
            "as a GROUP BY week";
    } elseif ($groupby == "day") {
        $report =
            "SELECT AVG(avg_size) AS avg_size, DAYOFYEAR(FROM_UNIXTIME(timeslice)) AS day FROM" .
            "(SELECT ip, AVG(response_size) as avg_size, ROUND((CEILING(UNIX_TIMESTAMP(date) / $sess) * $sess)) AS timeslice " .
                "FROM server_log GROUP BY ip, timeslice) " .
            "as a GROUP BY day";
    }
    return $report;
}

function set_status($mysqli, $ready, $size) {
    $status = 100 * ($ready / $size);
    $q = "UPDATE log_status SET status='$status' WHERE id=1";
    $mysqli->query($q);
}
