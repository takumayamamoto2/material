<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'review.php';
require_once MODEL_PATH . 'valid.php';

// セッション開始
session_start();
// セッションにIDがセットされていなければログインページへ飛ぶ
if(login_check() === false){
    redirect_to(LOGIN_URL);
}

// トークンがPOSTの値とセッションの値で同一であるか調べ、
// 検証後、セッションを破棄し、違っていればメッセージセット＆引数のページに飛ぶ
is_valid_csrf_token_check(DETAILS_URL);

// データベースの接続を確立
$db = getdb_connect();
// ユーザーデータをセッション関数とデータベースを使って取り出す
$user = login_user_data($db);


// レビューの商品IDが飛んできたらpostでキャッチ
$item_id = get_post('item_id');
// レビューのコメントが飛んできたらpostでキャッチ
$user_comment = get_post('comment');
// レビューの星が飛んできたらpostでキャッチ
$star = get_post('star');


// ユーザーからのレビューが正しい情報だったら、データベースに登録
if(validate_user_review($star, $user_comment) === true){
    if(review_transaction($db, $item_id, $user['user_id'], $user['user_name'], $user_comment, $star) === true){
        set_message('評価、コメントありがとうございました。');
    } else {
        set_error('評価、コメントの登録に失敗しました');
    }
}


redirect_to(DETAILS_URL);