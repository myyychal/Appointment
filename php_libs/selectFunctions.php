<?php

function selectUsers()
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM users ";

    $ret = $db->query($sql);

    return $ret;
}

function selectUsersWithPrivilege($privilege)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM users WHERE privilege=$privilege";

    $ret = $db->query($sql);

    return $ret;
}

function selectMeetingsByUser($userlogin)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM meetings WHERE name IN (SELECT meetingname FROM userscurves WHERE userlogin = \"$userlogin\" AND points=\"\")";

    $ret = $db->query($sql);

    return $ret;
}

function selectAllUsersEmailsRelatedToMeetingName($meetingName){
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT email FROM users WHERE login IN (SELECT userlogin FROM userscurves WHERE meetingname=\"$meetingName\")";

    $ret = $db->query($sql);

    $emailsString = "";
    while ($row = $ret->fetchArray(SQLITE3_ASSOC)){
        $emailsString .= $row["email"] . ", ";
    }

    return $emailsString;
}

function selectMeetingByName($name)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM meetings WHERE name='$name'";

    $ret = $db->query($sql);

    return $ret;
}

function selectAllMeetings()
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM meetings";

    $ret = $db->query($sql);

    return $ret;
}

function selectAllPointsFromMeeting($selectedMeetingName){

    $allPoints = "";

    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT points FROM userscurves WHERE meetingname='$selectedMeetingName'";

    $ret = $db->query($sql);

    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $allPoints = $allPoints . ":" . $row["points"];
    }

    return $allPoints;
}

?>