<?php

require_once MODEL_PATH . 'db.php';


// ユーザーのカート情報を取得
function get_user_cart($db, $user_id, $item_id){

  $sql="
    SELECT
      user_id,
      item_id,
      amount
    FROM 
      material_carts
    WHERE
      user_id = ?
      AND 
      item_id = ?
      ";
    
    return fetch_query($db, $sql, array($user_id, $item_id));
}

// ユーザーのカート情報を更新
function update_user_cart($db, $amount, $user_id, $item_id){

  $sql ="
    UPDATE 
      material_carts 
    SET  
      amount = ?
    WHERE  
      user_id = ? 
      AND 
      item_id = ?
      ";
    
    return execute_query($db, $sql, array($amount, $user_id, $item_id));
}

// ユーザーのカート情報を更新
function insert_user_cart($db, $user_id, $item_id, $amount){

  $sql ="
    INSERT INTO
      material_carts (
      user_id,
      item_id,
      amount )
    VALUES
      (?,?,?)
      ";
    
    return execute_query($db, $sql, array($user_id, $item_id, $amount));
}


// ユーザーのカート情報を更新
function get_user_cart_items($db, $user_id){

  $sql ="
  SELECT 
    material_items.id,
    material_items.name,
    material_items.price,
    material_items.filename,
    material_items.type,
    material_items.type2,
    material_items.comment,
    material_items.price,
    material_items.stock,
    material_items.status,
    material_carts.item_id,
    material_carts.amount
  FROM
    material_items
    INNER JOIN material_carts
    ON material_items.id = material_carts.item_id
  WHERE
    user_id = ?
    ";
    
    return fetch_query_all($db, $sql, array($user_id));
}


// SQL文を作成 material_itemsとmaterial_cartsの合計値を取得
function get_user_cart_sum($db, $user_id){

  $sql ="
  SELECT 
    SUM(material_items.price * material_carts.amount) AS price_sum,
    SUM(material_carts.amount) AS amount_sum

  FROM
    material_items
    INNER JOIN material_carts
    ON material_items.id = material_carts.item_id
  WHERE
    user_id = ?
    ";
    
    return fetch_query($db, $sql, array($user_id));
}

// 削除ボタンが押された商品のカート情報を削除
function user_cart_delete_item($db, $item_id, $user_id){

  $sql ="
  DELETE 
  FROM 
    material_carts 
  WHERE 
    item_id = ? 
  AND 
    user_id = ?
    ";
    
    return execute_query($db, $sql, array($item_id, $user_id));
}

// 削除ボタンが押された商品のカート情報を削除
function user_cart_amount_change($db, $amount, $item_id, $user_id){

  $sql ="
  UPDATE 
    material_carts 
  SET 
    amount = ? 
  WHERE 
    item_id = ? 
  AND 
    user_id = ?
    ";
    
    return execute_query($db, $sql, array($amount, $item_id, $user_id));
}

// 購入時の在庫落とし
function item_stock_decrease($db, $amount, $item_id){

  $sql="
  UPDATE 
    material_items
  SET 
    stock = stock - ? 
  WHERE id = ?
    ";
    
    return execute_query($db, $sql, array($amount, $item_id));
}

// ユーザーの購入情報を新規追加
function user_cart_history($db, $user_name, $user_id, $item_id, $amount){

  $sql = "
  INSERT INTO 
    material_item_history (
    user_name, 
    user_id, 
    item_id, 
    amount) 
  VALUES (?,?,?,?)
    ";
    
    return execute_query($db, $sql, array($user_name, $user_id, $item_id, $amount));
}

// ユーザーの商品のカート情報を削除
function user_cart_done($db, $user_id){

  $sql = "
  DELETE 
  FROM 
    material_carts 
  WHERE 
    user_id = ?
    ";

    return execute_query($db, $sql, array($user_id));
}



// カートに追加ボタンが押された時の処理　カートテーブルを更新or新規追加
function user_cart_add($db, $user_id, $item_id){
  // 現在のユーザーのカート情報を取得
  $user_cart = get_user_cart($db, $user_id, $item_id);
  if(is_array($user_cart)){
      $amount = $user_cart['amount'] + 1;
      update_user_cart($db, $amount, $user_id, $item_id);
  } else {
      $amount = $user_cart['amount'] + 1;
      insert_user_cart($db, $user_id, $item_id, $amount); 
  }
  return true;
}


// 商品購入時のトランザクション
function transaction_done($db, $user, $user_cart_items){
  $db->beginTransaction();
  if(item_done($db, $user, $user_cart_items) === false){
      $db->rollback();
      return false;
  }
  $db->commit();
  set_message('お買い上げありがとうございました！');
  return true;
  }

// 商品購入処理
function item_done($db, $user, $user_cart_items){
  foreach($user_cart_items as $value){
      if(item_stock_decrease($db, $value['amount'], $value['item_id']) === false){
          set_error('在庫数落としが失敗しました');
          return false;
      }
  }
  foreach($user_cart_items as $value){
      if(user_cart_history($db, $user['user_name'], $user['user_id'], $value['item_id'], $value['amount']) === false){
          set_error('購入履歴への書き込みが失敗しました');
          return false;
      }
  }
  if(user_cart_done($db, $user['user_id']) === false){
      set_error('ユーザーカート情報の消去が失敗しました');
      return false;
  }
  return true;
}

