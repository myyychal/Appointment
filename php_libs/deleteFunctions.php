<?php

function deleteProductsTypes($ids)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM productstypes WHERE id=$id";
        $ret = $db->query($sql);
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM products WHERE typeid=$id";
        $ret = $db->query($sql);
    }

    $db->close();

    return $ret;
}

function deleteProducts($ids)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM products WHERE id=$id";
        $ret = $db->query($sql);
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM comparisons WHERE firstproductid=$id";
        $ret = $db->query($sql);
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM comparisons WHERE secondproductid=$id";
        $ret = $db->query($sql);
    }

    $db->close();

    return $ret;
}

function deleteQuestionnaires($ids)
{
    $db = new SQLite3("db/db.sqlite3");
    if (!$db) {
        echo $db->lastErrorMsg();
        return false;
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM questionnaires WHERE id=$id";
        $ret = $db->query($sql);
    }

    foreach ($ids as $id) {
        $sql = "DELETE FROM comparisons WHERE questionnaireid=$id";
        $ret = $db->query($sql);
    }

    $db->close();

    return $ret;
}

?>