<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'review.php';
require_once MODEL_PATH . 'cart.php';

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


// ユーザーのカート情報をデータベースから取得
$user_cart_items = get_user_cart_items($db, $user['user_id']);

// 合計数量と合計金額 material_itemsとmaterial_cartsの合計値を取得
$user_cart_sum = get_user_cart_sum($db, $user['user_id']);

// 表示データをHTMLエンティティに変換する
$user_cart_items = entity_change($user_cart_items);

// トークンを取得する
get_csrf_token();
include_once VIEW_PATH . 'cart_view.php';