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

// トークンがPOSTの値とセッションの値で同一であるか調べ、
// 検証後、セッションを破棄し、違っていればメッセージセット＆引数のページに飛ぶ
is_valid_csrf_token_check(CART_URL);

// データベースの接続を確立
$db = getdb_connect();
// ユーザーデータをセッション関数とデータベースを使って取り出す
$user = login_user_data($db);

// どの商品の「削除」が押されたかを取得
$item_id = get_post('item_id');

// ユーザーのカート情報をデータベースから取得
if(user_cart_delete_item($db, $item_id, $user['user_id']) !== false){
    set_message('削除しました');
} else {
    set_error('削除に失敗しました');
}


redirect_to(CART_URL);