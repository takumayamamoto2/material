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


// イラストボタンか音楽ボタンどちらかが押されたらセッションに情報を保存
if(get_post('illust') == ITEM_TYPE['illust']){
    set_session('item_type', get_post('illust'));
} else if(get_post('music') == ITEM_TYPE['music']){
    set_session('item_type', get_post('music'));
}

// イラストボタンか音楽ボタンどちらも押されていなければ初期値にイラストを入れる
if(get_session('item_type') === ''){
    $type_bind = ITEM_TYPE['illust'];
} else {
// セッションに保存された商品の種類情報を入れる
$type_bind = get_session('item_type');
}


// 検索情報が飛んできたらpostでキャッチ
$search = get_post('search');
// 並び替え情報が飛んできたらpostでキャッチ
$sort = get_post('sort');
// 絞り込み情報が飛んできたらpostでキャッチ
$squeeze = get_post('squeeze');

// 商品一覧の商品検索・並べ替え・絞り込み
$item_data = item_search($db, $type_bind, $search, $sort, $squeeze);

// 表示データをHTMLエンティティに変換する
$item_data = entity_change($item_data);

if(empty($item_data) === true){
    set_error('該当商品はありませんでした');
}

// トークンを取得する
get_csrf_token();
include_once VIEW_PATH . 'item_list_view.php';