<?php

function getdb_connect(){

    // MySQL用のDSN文字列
    $dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST.';charset='. DB_CHARSET;

    try {
        // データベースに接続
        $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
    } catch (PDOException $e){
        exit('データベースに接続できませんでした。理由：' .$e->getMesseage() );
    }
    return $dbh;
}

// sqlを実行して一行のみ取得
function fetch_query($db, $sql, $params = array()){
    try {
        //SQL文実行する準備
        $stmt = $db->prepare($sql);
        // SQL実行
        $stmt->execute($params);
        // 取得
        return $stmt->fetch(); 
    } catch (PDOException $e) {
        set_error('取得に失敗しました'. $e);
    }
    return false;
}

// sqlを実行して全行取得
function fetch_query_all($db, $sql, $params = array()){
    try {
        //SQL文実行する準備
        $stmt = $db->prepare($sql);
        // SQL実行
        $stmt->execute($params);
        // 取得
        return $stmt->fetchAll(); 
    } catch (PDOException $e) {
        set_error('取得に失敗しました'. $e);
    }
    return false;
}

// sqlを実行して取得無し
function execute_query($db, $sql, $params = array()){
    try {
        //SQL文実行する準備
        $stmt = $db->prepare($sql);
        // SQL実行
        return $stmt->execute($params);
    } catch (PDOException $e) {
        set_error('取得に失敗しました'. $e);
    }
    return false;
}