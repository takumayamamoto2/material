<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'function.php';

// セッション開始
session_start();
// セッションにIDがセットされていなければログインページへ飛ぶ
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

//　商品一覧データを取得
$item_data = get_all_items($db);

// 表示データをHTMLエンティティに変換する
$item_data = entity_change($item_data);

// トークンを取得する
get_csrf_token();
include_once VIEW_PATH . 'admin_view.php';