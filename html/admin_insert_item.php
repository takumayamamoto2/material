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

// ポストの値を取得
$name = get_post('item_name');
$price         = get_post('price');
$file          = get_file('new_file');
$status        = get_post('release');
$stock         = get_post('stock');
$type2         = get_post('type2');
$star          = get_post('star');
$review_amount = get_post('review_amount');
$comment       = get_post('comment');

// 正しい値のチェック
if(validate_item($name, $price, $file, $status, $stock, $type2, $star, $review_amount, $comment) !== false){
    $db->beginTransaction();
    // ファイルの保存&チェック
    $new_filename = file_regist();
    // ファイルが画像か音楽かを取得
    $type = item_type_check($new_filename);
    if(insert_item($db, $name, $price, $new_filename, $status, $stock, $type, $type2, $star, $review_amount, $comment) === true){
        // この処理まで来たら成功メッセージを格納する
        set_message('追加成功'); 
        $db->commit();
    } else{
        set_error('追加失敗');
        $db->rollback();
    }
}

// 指定のページへ飛ばす
redirect_to(ADMIN_URL);