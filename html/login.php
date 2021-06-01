<?php

require_once '../conf/const.php';

// セッション開始
session_start();

// ログイン済みだったらホームページへリダイレクト
if(isset($_SESSION['user_id'])){
    header('Location: item_list.php');
    exit;
}

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST.';charset='. DB_CHARSET;


// 変数の初期化＆配列宣言
$err_msg  = array();  // エラーメッセージ
$success_msg = ''; // 成功メッセージ
$mode='';

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // アップロードした新しいデータを取得
    try {
        // データベースに接続
        $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // モード判別
        if(isset($_POST['mode'])){
            $mode = $_POST['mode'];
        }
        
        // ログイン処理
        if($mode === 'rogin'){
            
            //データベース取得処理
            try{
                // バインド値取得
                $user_name   = $_POST['user_name'];
                $password  = $_POST['password'];
                //SQL文
                $sql = 'SELECT * FROM material_users WHERE user_name = ? AND password = ?';
                //SQL文実行する準備
                $stmt = $dbh->prepare($sql);
                // バインドする値をセット
                $stmt->bindValue(1,$user_name,    PDO::PARAM_STR);
                $stmt->bindValue(2,$password,   PDO::PARAM_STR);
                // SQL実行
                $stmt->execute();
                // 取得
                $data = $stmt->fetch();
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
            
            // 該当するユーザーIDパスワードがあったかどうかチェック
            if($data === FALSE){
                $err_msg[] = 'ユーザーIDかパスワードが違います';
            } else if(isset($data['user_id'])){
                //セッション変数にユーザーIDを保存
                $_SESSION['user_id'] = $data['user_id'];
                // ユーザー名とパスワードが「admin」だった場合（ユーザーIDで判断）商品管理ページへ移行
                if($data['user_id'] === 1){
                    header('Location: admin.php');
                    exit;
                } else {
                    header('Location: item_list.php');
                    exit;
                }
            } else {
                $err_msg[] = '予期せぬエラー';
            }
            
        }
    } catch(PDOException $e){
      // 接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
    }
}

include_once VIEW_PATH . 'login_view.php';
