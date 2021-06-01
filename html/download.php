<?php

require_once '../conf/const.php';

$user_id = '';
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
$img_dir  = './assets/img/';  //アップロードした新しい画像ファイルの保存ディレクトリ
$bgm_dir  = './assets/bgm/';  //アップロードした新しい音楽ファイルの保存ディレクトリ
$item_data = '';  // 下に商品一覧を取得して表示させるための配列
$err_msg        = array();  // エラーメッセージ
$success_msg    = array(); // 処理成功メッセージ
$item_id = '';
$fpath = '';
$filename = '';



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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])){
        //既存のアップロードされたデータの取得
        try {
            
            // 飛んできたアイテムidをバインドの値にセット
            $item_id = '';
            if(isset($_POST['item_id']) === TRUE){
                $item_id = $_POST['item_id'];
            }
            
            // SQL文を作成 material_itemsとmaterial_item_historyを連結取得
            $sql ='SELECT 
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
                item_id = ?;';
                
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダに値をバインド
            $stmt->bindValue(1, $user_id,   PDO::PARAM_INT);
            $stmt->bindValue(2, $item_id,   PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $item_data = $stmt->fetch();
            
        }   catch (PDOException $e) {
            throw $e;
        }
        }
    } catch(PDOException $e){
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
    }


//画像のパスとファイル名
if($item_data['type'] === 1){
$fpath = $img_dir . $item_data['filename'];
} else if ($item_data['type'] === 2){
$fpath = $bgm_dir . $item_data['filename'];
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

exit;