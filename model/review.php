<?php

require_once MODEL_PATH . 'db.php';


// ユーザーからの商品のレビューを追加
function insert_user_comment($db, $item_id, $user_id, $user_name, $user_comment, $star){

  $sql="
    INSERT INTO 
      material_user_comment (
      item_id, 
      user_id,
      user_name, 
      user_comment,
      star) 
      VALUES (?,?,?,?,?)
      ";
      
    return execute_query($db, $sql, array($item_id, $user_id, $user_name, $user_comment, $star));
}

// 星を登録
function update_review_star($db, $star, $item_id){

  $sql="
    UPDATE 
      material_items 
    SET 
      star = star + ?, 
      review_amount = review_amount + 1
    WHERE id = ?
    ";
        
  return execute_query($db, $sql, array($star, $item_id));
}

// ユーザーの商品のレビューを取得
function get_user_review($db, $item_id){

    $sql="
      SELECT 
        user_name, 
        user_comment, 
        star, 
        createdate 
      FROM 
        material_user_comment 
      WHERE 
        item_id = ? 
      ORDER BY 
        createdate DESC
        ";
        
      return fetch_query_all($db, $sql, array($item_id));
  }


// ユーザーからのレビューをトランザクションで登録する
function review_transaction($db, $item_id, $user_id, $user_name, $user_comment, $star){
    $db->beginTransaction();
    if(insert_user_comment($db, $item_id, $user_id, $user_name, $user_comment, $star) !== false){
        $order_id = $db -> lastInsertId();
        if(update_review_star($db, $star, $item_id) === false){
            return false;
        }
    $db->commit();
    return true;
    }
$db->rollback();
return false;
}