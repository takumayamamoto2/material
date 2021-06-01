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
$data               = array();  // 下に商品一覧を取得して表示させるための配列
$err_msg            = array();  // エラーメッセージ
$success_msg        = array(); // 処理成功メッセージ
$login_regex       = '/^[a-zA-Z0-9]{6,}$/'; // 正規表現 半角英数字
$already_name   = '';

try {
        // データベースに接続
        $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
    // エラーチェック&アップロード画像ファイルの保存
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        
        // フィルタリング、エラーチェック
        if (isset($_POST['user_name'],$_POST['password'])) {
            
            // フォームからのデータ受け取り処理　データが入っていればPOSTの中身を変数に代入
            $name = '';
            if(isset($_POST['user_name']) === TRUE){
                $name = $_POST['user_name'];
            }
            
            $pass = '';
            if(isset($_POST['password']) === TRUE){
                $pass = $_POST['password'];
            }
            
            // データフィルタリング処理　全角や半角の空白を変換
            // str_replace(変換前,変換後,配列名)で文字を置換する(全角空白を半角空白に置き換え) trimで前後の空白を消す(半角空白のみ)
            $name = str_replace('　',' ',$name);
            $name = trim($name);
            $pass = str_replace('　',' ',$pass);
            $pass = trim($pass);
            
            // データのエラーチェック　エラーの場合は配列にエラー文字を格納していく
            
            // 同じ名前の登録者をデータベースから探す
            $sql='SELECT * FROM material_users WHERE user_name = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $name,                PDO::PARAM_INT);
            $stmt->execute();
            $already_name = $stmt->fetch();
            
            // 名前の処理
            if($name === ''){
                $err_msg[] = '名前を入力してください';
            } else if(preg_match($login_regex,$name) === 0){
                $err_msg[] = '名前は半角英数字6文字以上でお願いします';
            } else if($already_name > 0){
                $err_msg[] = '既に同じ名前のユーザーが登録されています';
            }
            
            // パスワードの処理
            if($pass === ''){
                $err_msg[] = 'パスワードを入力してください';
            } else if(preg_match($login_regex,$pass) === 0){
                $err_msg[] = 'パスワードは半角英数字6文字以上でお願いします';
            }
        }
    

        // エラーがなければ、データベース接続
        if (count($err_msg) === 0) {
            // 名前とパスワードを登録する
            try {
                
                //material_usersテーブルの更新処理
                
                // SQL文を作成
                $sql = 'INSERT INTO material_users(user_name,password,createdate,updatedate) VALUES(?,?,NOW(),NOW());';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $name,          PDO::PARAM_STR);
                $stmt->bindValue(2, $pass,          PDO::PARAM_STR);
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg[] = 'アカウント作成が完了しました';
            
            } catch(PDOException $e){
              // 接続失敗した場合
              $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
            }
        }
    }
} catch(PDOException $e){
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
}

include_once VIEW_PATH . 'register_view.php';