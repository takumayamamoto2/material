<?php

require_once MODEL_PATH . 'db.php';


// 商品情報を登録
function insert_item($db, $name, $price, $new_filename, $status, $stock, $type, $type2, $star, $review_amount, $comment){
    $sql = "
        INSERT INTO 
          material_items(
          name,
          price,
          filename,
          status,
          stock,
          type,
          type2,
          star,
          review_amount,
          comment)
        VALUES(?,?,?,?,?,?,?,?,?,?)
        ";
    
    return execute_query($db, $sql, array($name, $price, $new_filename, $status, $stock, $type, $type2, $star, $review_amount, $comment));
}

//既存のアップロードされた画像ファイル名、商品名、値段、個数、公開ステータスの取得
function get_all_items($db){
  $sql ="
    SELECT 
      material_items.id,
      material_items.name,
      material_items.price,
      material_items.filename,
      material_items.status,
      material_items.stock,
      material_items.type,
      material_items.type2,
      material_items.star,
      material_items.review_amount,
      material_items.comment
    FROM
      material_items
      ";
  
  return fetch_query_all($db,$sql);
}

// 商品IDをいれるとその商品の情報を取得
function get_item($db, $item_id){
  $sql ="
    SELECT 
      material_items.id,
      material_items.name,
      material_items.price,
      material_items.filename,
      material_items.status,
      material_items.stock,
      material_items.type,
      material_items.type2,
      material_items.star,
      material_items.review_amount,
      material_items.comment
    FROM
      material_items
    WHERE
    material_items.id = ?
      ";
  
  return fetch_query($db, $sql, array($item_id));
}

// 商品の削除
function delete_item($db, $item_id){

  $sql = "
  DELETE 
  FROM 
   material_items 
  WHERE 
   id = ?
  ";
  
  return execute_query($db, $sql, array($item_id));
}

// 商品の在庫数量を変更
function item_stock($db, $stock, $item_id){

  $sql = "
  UPDATE
   material_items
  SET
   stock = ? 
  WHERE
   id = ?
  ";
  
  return execute_query($db, $sql, array($stock, $item_id));
}

// 商品の公開ステータスを変更
function item_status($db, $status, $item_id){
  $status_param = ITEM_STATUS[$status];

  $sql = "
  UPDATE
   material_items
  SET
   status = ? 
  WHERE
   id = ?
  ";
  
  return execute_query($db, $sql, array($status_param, $item_id));
}

// 商品一覧ページの商品データ取得
function get_item_data($db, $type = ITEM_TYPE['illust'], $squeeze = '%'){

  $sql ="
  SELECT COUNT(id) AS search_result,
    material_items.id,
    material_items.name,
    material_items.price,
    material_items.filename,
    material_items.status,
    material_items.stock,
    material_items.type,
    material_items.type2,
    material_items.star,
    material_items.review_amount,
    material_items.comment
  FROM
    material_items
  WHERE
    material_items.status = 1 AND
    material_items.type = ? AND
    material_items.type2 LIKE ?
    GROUP BY material_items.id
    ";

  return fetch_query_all($db, $sql, array($type, $squeeze));
}

// 商品一覧ページの商品データ取得(並び替え)
function get_item_data_sort($db, $type = ITEM_TYPE['illust'], $sort = false){

  $sql ="
  SELECT COUNT(id) AS search_result,
    material_items.id,
    material_items.name,
    material_items.price,
    material_items.filename,
    material_items.status,
    material_items.stock,
    material_items.type,
    material_items.type2,
    material_items.star,
    material_items.review_amount,
    material_items.comment,
    material_items.createdate
  FROM
    material_items
  WHERE
    material_items.status = 1 AND
    material_items.type = ?
    GROUP BY material_items.id";
  if($sort === 'review'){ $sql .=" ORDER BY star/review_amount DESC ";} else
  if($sort === 'price'){ $sql .=" ORDER BY price DESC ";} else
  if($sort === 'new'){ $sql .=" ORDER BY id DESC ";} else
  if($sort === 'type'){ $sql .=" ORDER BY type2 ";}

  return fetch_query_all($db, $sql, array($type));
}

// 商品一覧ページの商品データ取得(絞り込み)
function get_item_data_squeeze($db, $type = ITEM_TYPE['illust'], $squeeze = false){

  $sql ="
  SELECT COUNT(id) AS search_result,
    material_items.id,
    material_items.name,
    material_items.price,
    material_items.filename,
    material_items.status,
    material_items.stock,
    material_items.type,
    material_items.type2,
    material_items.star,
    material_items.review_amount,
    material_items.comment,
    material_items.createdate
  FROM
    material_items
  WHERE
    material_items.status = 1 AND
    material_items.type = ?
  ";
  if($squeeze === '3.5'){ $sql .=" AND (star/review_amount) >= ? GROUP BY material_items.id";} else
  if($squeeze === '0'  ){ $sql .=" AND material_items.price = ? GROUP BY material_items.id ";} else
  if($squeeze === '300'){ $sql .=" AND material_items.price <= ? GROUP BY material_items.id ";} else
  if($squeeze === '500'){ $sql .=" AND material_items.price <= ? GROUP BY material_items.id ";}

  return fetch_query_all($db, $sql, array($type, $squeeze));
}


// 商品一覧の商品検索・並べ替え・絞り込み
function item_search($db, $type_bind, $search, $sort, $squeeze ){
  // 商品一覧データを取得
  if($search !== ''){
      return $item_data = get_item_data($db, $type_bind, $search);

  // 商品の並び替えが押されたら
  } else if($sort !== ''){
    return $item_data = get_item_data_sort($db, $type_bind, $sort);

  // 商品の絞り込みが押されたら
  } else if($squeeze !== ''){
      // 整数が入ってきたら絞り込み
      if(is_numeric($squeeze) === true){
        return  $item_data = get_item_data_squeeze($db, $type_bind, $squeeze);
      // 文字列が入ってきたら文字列の絞り込み
      } else if(gettype($squeeze) === 'string'){
        return  $item_data = get_item_data($db, $type_bind, $squeeze);
      }

  // どの検索ボタンも押されてない、もしくは空の情報が飛んできたら
  } else {
      return $item_data = get_item_data($db, $type_bind);
  } 
}