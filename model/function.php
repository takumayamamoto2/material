<?php

// getの中身を返す
function get_get($name){
  if(isset($_GET[$name]) === true){
    return $_GET[$name];
  }
  return '';
}

// postの中身を返す
function get_post($name){
  if(post_check() === true){
    if(isset($_POST[$name]) === true){
        return $_POST[$name];
    }
    return '';
  }
  return '';
}

// fileの中身を返す
function get_file($name){
  if(post_check() === true){
    if(isset($_FILES[$name]) === true){
        return $_FILES[$name];
    }
  return '';
  }
}

// セッションに保存されている名前を入れたら$_SESSIONのセットを返す
function get_session($name){
    // 入れた名前の中身が$_SESSIONに入っているかどうかを確認
    if(isset($_SESSION[$name]) === true){
      // 入れた名前で保存されているセッションを返す
      return $_SESSION[$name];
    };
    // $_SESSIONの中身が無かったら空文字を返す
    return '';
}

// postデータかどうか確認
function post_check(){
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        return false;
    }
    return true;
}

// 指定のページへ飛ばす
function redirect_to($url){
    header('Location: ' . $url);
    exit;
}

// 名前と値を入れるとセッションに値を保存する
function set_session($name, $value){
  $_SESSION[$name] = $value;
}

// 引数にエラーメッセージを入れるとセッションにメッセージを保存できる
function set_error($err_msg){
  $_SESSION['errors'][] = $err_msg;
}

// セッションに保存されているエラーメッセージの取得
function get_errors(){
  // セッションに保存されているエラーメッセージを取得
  $err_msg = get_session('errors');
  // エラーメッセージが無かったら空の配列を返す
  if($err_msg === ''){
    return array();
  }
  // エラーメッセージを返す
  return $err_msg;
}

// エラーメッセージ消去
function delete_errors(){
    // セッションに空の配列を保存（次回以降のエラーメッセージの初期化）
  set_session('errors',  array());
}


// 引数にエラーメッセージを入れるとセッションにメッセージを保存できる
function set_message($suc_msg){
  $_SESSION['success'][] = $suc_msg;
}

// セッションに保存されているエラーメッセージの取得
function get_messages(){
  // セッションに保存されているエラーメッセージを取得
  $suc_msg = get_session('success');
  // エラーメッセージが無かったら空の配列を返す
  if($suc_msg === ''){
    return array();
  }
  // エラーメッセージを返す
  return $suc_msg;
}

// エラーメッセージ消去
function delete_messages(){
    // セッションに空の配列を保存（次回以降のエラーメッセージの初期化）
  set_session('success',  array());
}

// 購入商品をダウンロードする
function item_download($item_data){

  //画像のパスとファイル名
  if($item_data['type'] === ITEM_TYPE['illust']){
      $fpath = IMG_PATH . $item_data['filename'];
  } else if ($item_data['type'] === ITEM_TYPE['music']){
      $fpath = BGM_PATH . $item_data['filename'];
  } else {
      set_error('ファイルタイプの識別に失敗しました');
      return false;
  }
  
  // データベースから取り出したファイル名から拡張子のみを拾う
  $extension = pathinfo($item_data['filename'], PATHINFO_EXTENSION);
  // データベースから取り出した日本語のファイル名
  $filename = $item_data['name'];
  
  // 画像のダウンロード
  header('Content-Type: application/octet-stream');
  // ファイルパスを指定
  header('Content-Length: '.filesize($fpath));
  
  // ダウンロード時のファイル名 (用意した日本語のファイル名と拡張子を結合)
  header('Content-disposition: attachment; filename="'.$filename.'.'.$extension);
  // 出力
  readfile($fpath);
  
  return true;
}


// 特殊文字をHTMLエンティティに変換
function entity_str($str){
  return  htmlspecialchars($str,ENT_QUOTES,'UTF-8');
}

// 一次元配列の特殊文字をHTMLエンティティに変換
// 一次元配列の値を取り出して変換する
function entity_change_one($one_array) {
  // 一次元配列の値を取り出す
  foreach ($one_array as $key => $value) {
      // 特殊文字をHTMLエンティティに変換
      $one_array[$key] = entity_str($value);
  }
  return $one_array;
}

// 二次元配列の特殊文字をHTMLエンティティに変換
// 一次元配列の値のみを取り出して変換する)
function entity_change($two_array) {
  // 二次元配列を一次元配列にする
  foreach ($two_array as $key => $value) {
    // 一次元配列の値のみを取り出す	  
    foreach ($value as $keys => $values) {
      // 特殊文字をHTMLエンティティに変換
      $two_array[$key][$keys] = entity_str($values);
    }
  }
  
  return $two_array;
}

// 20文字のランダムな値を取得
function get_random_string($length = 20){
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

  // トークンの生成…ランダムな文字列を生成し、セッションに保存
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。30文字のランダムな文字列を生成
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。'csrf_token'という名で、セッションにランダムな文字列をセット
  set_session('csrf_token', $token);
  return $token;
}

// トークンのチェック…ユーザーがフォームデータを送った時、POSTの中身のトークンを確認
function is_valid_csrf_token($token){
  // POSTで送られてきたトークンが入っていなかったらfalseを返す
  if($token === '') {
    return false;
  }
  // get_session()はユーザー定義関数
  // ユーザーが送ったフォームデータのトークンと生成時のトークンが同じか確認
  return $token === get_session('csrf_token');
}

// トークンがPOSTの値とセッションの値で同一であるか調べ、
// 検証後、セッションを破棄し、違っていれば引数のページに飛ぶ
function is_valid_csrf_token_check($redirect){
  // ポストから受信したトークンを取得
  $token = get_post('csrf_token');
  // POSTのトークンとセッションに保存したトークンが同一であるか検証
  if(is_valid_csrf_token($token) === false){
    // 現在のセッションに保存されているトークンを破棄
    unset($_SESSION['csrf_token']);
    set_error('不正な処理が行われています');
    // トークンが違っていたらその後の処理を行わずにカートページへ飛ぶ
    redirect_to($redirect);
  } else{
  // 現在のセッションに保存されているトークンを破棄
  unset($_SESSION['csrf_token']);
  }
}
