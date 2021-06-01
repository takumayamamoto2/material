<?php

require_once '../conf/const.php';

// セッション開始
session_start();
// セッション変数からuser_idを取得
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
// 非ログインの場合はログインページへリダイレクト
    header('Location: login.php');
    exit;
}

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST.';charset='. DB_CHARSET;

// 変数の初期化＆配列宣言
$img_dir  = '../assets/img/';  //アップロードした新しい画像ファイルの保存ディレクトリ
$bgm_dir  = '../assets/bgm/';  //アップロードした新しい音楽ファイルの保存ディレクトリ
$data     = array();  // 下に商品一覧を取得して表示させるための配列
$err_msg            = array();  // エラーメッセージ
$success_msg        = array(); // 処理成功メッセージ
$type_bind = 1; // バインドの初期値（イラスト）
$number_regex       = '/^[0-9]+$/'; // 正規表現 半角数字
$mode='';
$item_id = '';

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    //処理モードの判別
    if(isset($_POST['mode'])){
        $mode = $_POST['mode'];
    }
    
    // 在庫数変更ボタンを押した時の在庫数の保存処理
    if ($mode === 'amount_change') {
        // フォームからのデータ受け取り処理　データが入っていれば$POSTの中身を変数に代入
        $amount_change = '';
        $amount_change = $_POST['amount_change'];
        
        // データフィルタリング処理　全角や半角の空白を変換
        // str_replace(変換前,変換後,配列名)で文字を置換する（全角空白を半角空白に置き換え）trimで前後の空白を消す(半角空白のみ)
        $amount_change = str_replace('　',' ',$amount_change);
        $amount_change = trim($amount_change);
        
        // データのエラーチェック エラーの場合は配列にエラー文字を格納していく
        if($amount_change === ''){
            $err_msg[] = '個数を入力してください';
        }  else if(preg_match($number_regex,$amount_change) === 0){
            $err_msg[] = '個数は半角数値を入力してください';
        }   else if($amount_change == 0){
            $err_msg[] = '個数は1以上の整数を入力してください';
        }
    }
}

// アップロードした新しいデータを取得
try {
        // データベースに接続
        $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    try {
        // ユーザー名の取得
        
        //SQL文
        $sql = 'SELECT * FROM material_users WHERE user_id = ?';
        //SQL文実行する準備
        $stmt = $dbh->prepare($sql);
        // バインドする値をセット
        $stmt->bindValue(1,$user_id,    PDO::PARAM_INT);
        // SQL実行
        $stmt->execute();
        // 取得
        $name_data = $stmt->fetch();
        // ユーザー名の取得確認
        if (isset($name_data['user_name'])){
            $user_name = $name_data['user_name'];
        } else {
            //取得出来なかった場合はログアウト処理
            header('Location: logout.php');
            exit;
        }
            
    }   catch (PDOException $e) {
            throw $e;
    }
    // エラーがなければ、登録処理
    if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        
        // 在庫数変更ボタンを押した時の在庫数の保存処理
        if ($mode === 'amount_change') {
            try {
                
                // バインドする値をセット
                $item_id = $_POST['item_id'];
                
                // SQL文を作成
                $sql = 'UPDATE material_carts SET amount = ?, createdate = NOW() WHERE item_id = ? AND user_id = ?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $amount_change,          PDO::PARAM_INT);
                $stmt->bindValue(2, $item_id,          PDO::PARAM_INT);
                $stmt->bindValue(3, $user_id,          PDO::PARAM_INT);
                
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg[] = '数量を変更しました';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
        }
        // 削除ボタン処理
         if($mode === 'delete') {
            try{
                // バインドする値をセット
                $item_id = $_POST['item_id'];
                
                // SQL文を作成
                $sql = 'DELETE FROM material_carts WHERE item_id = ? AND user_id =?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $item_id,   PDO::PARAM_INT);
                $stmt->bindValue(2, $user_id,   PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                // 成功メッセージ
                $success_msg[] = '削除しました';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
        }
    }
        //既存のアップロードされたデータの取得
        try {
            
            // SQL文を作成 material_itemsを取得
            $sql ='SELECT 
            	material_items.id,
                material_items.name,
                material_items.price,
                material_items.filename,
                material_items.type,
                material_items.type2,
                material_items.comment,
                material_carts.item_id,
                material_items.price,
                material_carts.amount
            FROM
                material_items
                INNER JOIN material_carts
                ON material_items.id = material_carts.item_id
            WHERE
                user_id = ?;';
                
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダに値をバインド
            $stmt->bindValue(1, $user_id,   PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                $data[] = $row;
            }
            
            // SQL文を作成 material_itemsとmaterial_cartsの合計値を取得
            $sql ='SELECT 
                SUM(material_items.price * material_carts.amount) AS price_sum,
                SUM(material_carts.amount) AS amount_sum

            FROM
                material_items
                INNER JOIN material_carts
                ON material_items.id = material_carts.item_id
            WHERE
                user_id = ?;';
                
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダに値をバインド
            $stmt->bindValue(1, $user_id,   PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $sum = $stmt->fetch();
            
        }   catch (PDOException $e) {
            throw $e;
        }
    } catch(PDOException $e){
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
    }

include_once VIEW_PATH . 'cart_view.php';