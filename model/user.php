<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'db.php';
require_once MODEL_PATH . 'function.php';


// ユーザーIDを入れるとユーザーの名前をデータベースから持ってくる
function get_user($db, $user_id){
    
    $sql = "
        SELECT
          user_id,
          user_name 
        FROM 
          material_users 
        WHERE 
          user_id = ?
        LIMIT 1
        ";
    
    return fetch_query($db, $sql, array($user_id));
}

// ユーザーIDを入れるとユーザーの名前をデータベースから持ってくる
function get_all_user($db){
    
  $sql = "
      SELECT
        user_name,
        createdate 
      FROM 
        material_users 
      ";
  
  return fetch_query_all($db, $sql);
}

// ユーザーの名前とパスワードをデータベースから持ってくる
function get_user_data($db, $user_name){
    
  $sql = "
      SELECT
        user_id,
        user_name,
        password
      FROM 
        material_users
      WHERE
        user_name = ?
      LIMIT 1
      ";
  
  return fetch_query($db, $sql, array($user_name));
}

// ユーザーの名前とパスワードを登録
function insert_user_data($db, $user_name, $password){
    
  $sql = "
      INSERT INTO 
        material_users (
        user_name,
        password )
      VALUE 
        (?, ?)
      ";
  
  return execute_query($db, $sql, array($user_name, $password));
}

// 管理者ユーザーのログインチェック
function login_check(){
    // セッション変数にユーザーIDがあるかどうか
    return get_session('user_id') !== '';
}

function admin_login_check(){
    // セッション変数にユーザーIDがあるかどうか
    return get_session('user_id') !== USER_NUMBER_ADMIN;
}

// ユーザータイプを判断(管理者であればTRUE)
function is_admin($user){
  return $user['user_id'] === USER_NUMBER_ADMIN;
}

// ユーザー名の取得確認。データベースから取得できていなければログアウト
function login_user_data($db){
  $user_id = get_session('user_id');
  return get_user($db, $user_id);
}

// 送信されたユーザーデータがデータベースにあるかどうか
function user_data_check($db, $user_name,$password){
  $user_data = get_user_data($db, $user_name);
  if( $user_data === false || $user_data['password'] !== $password){
    return false;
  }
  set_session('user_id', $user_data['user_id']);
  return $user_data;
}

// ユーザーの購入履歴を取得
function user_done_history($db, $user_id){
    
  $sql = "
  SELECT 
    material_items.id,
    material_items.name,
    material_items.filename,
    material_items.type,
    material_items.type2,
    material_items.comment,
    material_item_history.item_id,
    SUM(material_items.price) AS price_sum,
    SUM(material_item_history.amount) AS amount_sum
  FROM
    material_items
    INNER JOIN material_item_history
    ON material_items.id = material_item_history.item_id
  WHERE
    user_id = ?
  GROUP BY
    material_items.id
      ";
  
  return fetch_query_all($db, $sql, array($user_id));
}

// ユーザーの購入履歴を取得
function user_purchase_history($db, $user_id, $item_id){
    
  $sql ="
  SELECT 
    material_items.id,
    material_items.name,
    material_items.filename,
    material_items.type,
    material_item_history.user_id,
    material_item_history.item_id
  FROM
    material_items
    INNER JOIN material_item_history
    ON material_items.id = material_item_history.item_id
  WHERE
    user_id = ? AND
    item_id = ?
  LIMIT 1
    ";
  
  return fetch_query($db, $sql, array($user_id, $item_id));
}

