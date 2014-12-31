<?php

function checkLoginAndPassword($login, $password)
{
    $hash = "";

    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT login,password FROM users WHERE login = \"$login\"";

    $ret = $db->query($sql);
    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $hash = $row["password"];
    }

    if (crypt($password, $hash) === $hash) {
        return true;
    } else {
        return false;
    }

    $db->close();
}

function getUserPrivilege($login){
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    $sql = "SELECT login,privilege FROM users WHERE login = \"$login\"";

    $ret = $db->query($sql);
    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        $privilege = $row["privilege"];
    }

    return $privilege;
}

function loginUser($login, $password, $privilege)
{
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $login;
    $_SESSION['privilege'] = $privilege;
}

?>