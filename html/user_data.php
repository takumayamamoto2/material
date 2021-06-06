<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'valid.php';

// セッション開始
session_start();
// セッションにセットされているIDが管理者かどうかチェック
if(login_check() === false){
    redirect_to(LOGIN_URL);
}

// データベースの接続を確立
$db = getdb_connect();
// ユーザーデータをセッション関数とデータベースを使って取り出す
$user = login_user_data($db);

// ユーザータイプを判断(管理者であればTRUE)
if(is_admin($user) === false){
    redirect_to(LOGIN_URL);
}

// ユーザーデータ一覧を取得する
$user_data = get_all_user($db);

// 表示データをHTMLエンティティに変換する
$user_data = entity_change($user_data);

include_once VIEW_PATH . 'user_data_view.php';