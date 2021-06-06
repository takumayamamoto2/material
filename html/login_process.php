<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'item.php';
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

// 送信された名前がデータベースにあるかどうか、パスワードがあっているかチェック
$user_data = user_data_check($db, $user_name,$password);
// 違っていたらログインページ
if($user_data === false){
    set_error('ユーザーIDかパスワードが違います');
    redirect_to(LOGIN_URL);
}

// ユーザータイプを判断(管理者IDだったら管理者ページ)
if(is_admin($user_data) === true){
    set_message('ログインしました');
    redirect_to(ADMIN_URL);
}

set_message('ログインしました');
redirect_to(HOME_URL);
