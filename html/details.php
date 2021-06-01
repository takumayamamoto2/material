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
$name_data  = '';  // ユーザー名の取得用
$come_data  = array();  // 下にコメント一覧を取得して表示させるための配列
$err_msg  = array();  // エラーメッセージ
$success_msg = ''; // 成功メッセージ
$amount = 0;    //バインドする値をセット ユーザーが初めてその商品をクリックしたときの値
$mode='';

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    //処理モードの判別
    if(isset($_POST['mode'])){
        $mode = $_POST['mode'];
    }
        $item_id = '';
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
    
    if($mode === 'review'){
        // フォームからのデータ受け取り処理　データが入っていれば$POSTの中身を変数に代入
        $come = '';
        if(isset($_POST['comment']) === TRUE){
            $come = $_POST['comment'];
        }
        
        $item_id = '';
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        
        $star = '';
        if(isset($_POST['star']) === TRUE){
            $star = $_POST['star'];
        }
        // データフィルタリング処理　全角や半角の空白を変換
        // str_replace(変換前,変換後,配列名)で文字を置換する(全角空白を半角空白に置き換え) trimで前後の空白を消す(半角空白のみ)
        $come = str_replace('　',' ',$come);
        $come = trim($come);
        
        // データのエラーチェック エラーの場合は配列にエラー文字を格納していく
        
        // コメントの処理
        if($come === ''){
            $err_msg[] = 'コメントを入力してください';
        } else if(mb_strlen($come) > 1000){
            $err_msg[] = 'コメントは1000文字以内で入力してください';
        }
        
        // 星の値　3以外の数値が入ってきたらエラーメッセージ
        if($star !== '1' && $star !== '2' && $star !== '3' && $star !== '4' && $star !== '5' ){
            $err_msg[] = '不正な処理です';
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
    

    
    
    // エラーがなければ、POST処理 コメント、評価の登録
    if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        //評価コメントを送信を押したときの処理
        if($mode === 'review'){
            // トランザクション
            $dbh->beginTransaction();
            try {
                // コメントの登録
                // SQL文を作成
                $sql = 'INSERT INTO material_user_comment (item_id,user_name, user_id, user_comment,star, createdate) VALUES (?,?,?,?,?,NOW())';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1,$item_id,        PDO::PARAM_INT);
                $stmt->bindValue(2,$user_name,      PDO::PARAM_STR);
                $stmt->bindValue(3,$user_id,       PDO::PARAM_INT);
                $stmt->bindValue(4,$come,          PDO::PARAM_STR);
                $stmt->bindValue(5,$star,          PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                
                // 星を登録
                // SQL文を作成
                $sql = 'UPDATE material_items SET star = star + ?, review_amount = review_amount + 1, updatedate = NOW() WHERE id = ?';
                
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1,$star,      PDO::PARAM_INT);
                $stmt->bindValue(2,$item_id,      PDO::PARAM_INT);
                
                // SQLを実行
                $stmt->execute();
                // コミット
                $dbh->commit();
                $success_msg = '評価、コメントありがとうございました。';
            }   catch (PDOException $e) {
                // トランザクションロールバック
                $dbh->rollback();
                // 例外をスロー
                throw $e;
            }
        }
    }
    // エラーがなければ、POST処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
        //カート追加を押したときの処理
        if($mode ==='add_cart'){
            
            // バインドする値をセット
            if(isset($_POST['item_id']) === TRUE){
                $item_id = $_POST['item_id'];
            }
            
            try {
                //material_cartsテーブルの更新処理
                
                // 押されたデータを（どのユーザーがどの商品を押したか）カートテーブルの中から一致するものを探す
                $sql='SELECT * FROM material_carts WHERE user_id = ? AND item_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_id,                PDO::PARAM_INT);
                $stmt->bindValue(2, $item_id,                PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                
                // 既にカート内に商品があれば更新処理（amount＋１）無ければ新規追加
                if(count($rows)>0){
                    $amount = $rows[0]['amount'] + 1;
                    
                    //アップデート
                    $sql ='UPDATE material_carts 
                           SET user_id = ?, item_id = ?, amount = ?, createdate = NOW(), updatedate = NOW()
                           WHERE  user_id = ? AND item_id = ?';
                           
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    
                    // SQL文のプレースホルダに値をバインド
                    $stmt->bindValue(1, $user_id,           PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id,           PDO::PARAM_INT);
                    $stmt->bindValue(3, $amount,            PDO::PARAM_INT);
                    $stmt->bindValue(4, $user_id,           PDO::PARAM_INT);
                    $stmt->bindValue(5, $item_id,           PDO::PARAM_INT);
                }else{
                    //カート内に新規追加
                    
                    $amount++;
                    // SQL文を作成
                    $sql = 'INSERT INTO material_carts (user_id,item_id,amount,createdate,updatedate) VALUES(?,?,?,NOW(),NOW()) ;';
                                
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    // SQL文のプレースホルダに値をバインド
                    $stmt->bindValue(1, $user_id,                PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id,                PDO::PARAM_INT);
                    $stmt->bindValue(3, $amount,            PDO::PARAM_INT);
                    
                }
                
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg = 'カートに追加しました';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
        }
    }
            // データベースにある「名前」「コメント」「星」「時間」をSQL文で持ってくる
            try {
                // SQL文を作成
                $sql = 'SELECT user_name, user_comment, star, createdate FROM material_user_comment WHERE item_id = ? ORDER BY createdate DESC';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                //  SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1,$item_id,  PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                // レコードの取得
                $come_data = $stmt->fetchAll();
        
            }   catch (PDOException $e) {
                throw $e;
            }
          
            //既存のアップロードされたデータの取得
            try {
                
                // SQL文を作成 material_itemsを取得
                $sql ='SELECT
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
                    material_items.id = ?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $item_id,  PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                // レコードの取得
                $rows = $stmt->fetch();
                
            }   catch (PDOException $e) {
                throw $e;
            }
        
} catch(PDOException $e){
  // 接続失敗した場合
  $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
}

include_once VIEW_PATH . 'details_view.php';