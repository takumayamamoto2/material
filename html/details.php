<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'function.php';
require_once MODEL_PATH . 'review.php';


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


// イラストボタンか音楽ボタンどちらも押されていなければ初期値にイラストを入れる
if(get_session('item_type') === ''){
    $type_bind = ITEM_TYPE['illust'];
} else {
// セッションに保存された商品の種類情報を入れる
$type_bind = get_session('item_type');
}

// もしpostに商品IDが来たら
if(get_post('item_id') !== ''){
    $item_id_session = get_post('item_id');
    set_session('item_id', $item_id_session);
} 

// セッションから商品IDを取得
$item_id = get_session('item_id');

// コメントデータをデータベースから取得
$come_data = get_user_review($db, $item_id);
// 商品データをデータベースから取得
$item_data = get_item($db, $item_id);


// 表示データをHTMLエンティティに変換する
$come_data = entity_change($come_data);
$item_data = entity_change_one($item_data);

// トークンを取得する
get_csrf_token();
include_once VIEW_PATH . 'details_view.php';