<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'valid.php';

// セッション開始
session_start();
// セッションにIDがセットされていれば商品一覧ページへ飛ぶ
if(login_check() === true){
    redirect_to(HOME_URL);
}

// postからユーザーの名前を取得
$user_name = get_post('user_name');
// postからユーザーのパスワードを取得
$password = get_post('password');
// データベースの接続を確立
$db = getdb_connect();

// 入力した名前とパスワードが有効かどうかチェック
if(validate_user_resister($db, $user_name, $password) !== false){
    // データベースに登録
    insert_user_data($db, $user_name, $password);
    set_message('アカウント作成が完了しました');
}

// 指定のページへ飛ばす
redirect_to(REGIST_URL);
