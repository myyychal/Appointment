<?php

include 'selectFunctions.php';
include 'insertFunctions.php';

function updateUserMeetingWithPoints($userlogin, $meetingname, $points){
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "UPDATE userscurves SET points=\"$points\" WHERE userlogin='$userlogin' AND meetingname='$meetingname'";

    $ret = $db->exec($sql);
    if ($ret > 0) {
        $db->close();
        return true;
    } else {
        $db->close();
        return false;
    }
}

?>