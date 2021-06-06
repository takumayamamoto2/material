<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'cart.php';
require_once MODEL_PATH . 'valid.php';

// セッション開始
session_start();
// セッションにIDがセットされていなければログインページへ飛ぶ
if(login_check() === false){
    redirect_to(LOGIN_URL);
}

// トークンがPOSTの値とセッションの値で同一であるか調べ、
// 検証後、セッションを破棄し、違っていればメッセージセット＆引数のページに飛ぶ
is_valid_csrf_token_check(PURCHASE_URL);

// データベースの接続を確立
$db = getdb_connect();
// ユーザーデータをセッション関数とデータベースを使って取り出す
$user = login_user_data($db);


// どの商品が押されたかをキャッチ
$item_id = get_post('item_id');



// ユーザーの購入情報をデータベースから取得
$item_data = user_purchase_history($db, $user['user_id'], $item_id);

if(item_download($item_data) === false){
    set_error('ダウンロードに失敗しました');
} else {
    ser_message($item_data['name'] .'をダウンロードしました');
}

redirect_to(PURCHASE_URL);
