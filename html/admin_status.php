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

// トークンがPOSTの値とセッションの値で同一であるか調べ、
// 検証後、セッションを破棄し、違っていればメッセージセット＆引数のページに飛ぶ
is_valid_csrf_token_check(ADMIN_URL);

// データベースの接続を確立
$db = getdb_connect();
// ユーザーデータをセッション関数とデータベースを使って取り出す
$user = login_user_data($db);

// バインドする値をセット
$item_id = get_post('item_id');
$status = get_post('status');


// 公開ステータスの変更
if($status === 'open'){
    item_status($db, $status, $item_id);
    set_message('ステータスの変更に成功しました');
} else if($status === 'close'){
    item_status($db, $status, $item_id);
    set_message('ステータスの変更に成功しました');
} else {
    set_error('不正なステータスです');
}

// 指定のページへ飛ばす
redirect_to(ADMIN_URL);