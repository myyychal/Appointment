<?php

function addUser($username, $password, $privilege, $newEmail)
{
    global $errMsg;

    $cost = 10;
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $salt = sprintf("$2a$%02d$", $cost) . $salt;
    $hash = crypt($password, $salt);

    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT login,password FROM users WHERE login = \"$username\"";

    $ret = $db->query($sql);
    if ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $errMsg = "There is already a user with this login.";
        return false;
    }

    $sql = "INSERT INTO users VALUES (\"$username\", \"$hash\", \"$newEmail\", $privilege)";

    $ret = $db->exec($sql);
    if ($ret > 0) {
        $db->close();
        return true;
    } else {
        $db->close();
        return false;
    }
}

function addMeeting($name, $startDate, $startHour, $endHour, $sectionLength, $newDescription)
{
    global $errMsg;

    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM meetings WHERE name = \"$name\"";

    $ret = $db->query($sql);
    if ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $errMsg = "There is already a meeting with this name.";
        return false;
    }

    $sql = "INSERT INTO meetings VALUES (\"$name\", \"$startDate\", \"$startHour\", \"$endHour\", \"$sectionLength\")";

    $ret = $db->exec($sql);

    if ($ret > 0) {
        $db->close();
        return true;
    } else {
        $db->close();
        return false;
    }
}

function addUserToMeeting($userlogin, $meetingName){
    global $errMsg;

    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT * FROM userscurves WHERE userlogin = \"$userlogin\" and meetingname=\"$meetingName\"";

    $ret = $db->query($sql);
    if ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $errMsg = "There is already a meeting with this name.";
        return false;
    }

    $sql = "INSERT INTO userscurves VALUES (\"$userlogin\", \"$meetingName\", \"\")";

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