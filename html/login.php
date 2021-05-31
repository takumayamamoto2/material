<?php

// セッション開始
session_start();

// ログイン済みだったらホームページへリダイレクト
if(isset($_SESSION['user_id'])){
    header('Location: item_list.php');
    exit;
}

$host     = 'localhost';
$username = 'codecamp44071';   // MySQLのユーザ名
$password = 'codecamp44071';   // MySQLのパスワード
$dbname   = 'codecamp44071';   // MySQLのDB名
$charset  = 'utf8';  // データベースの文字コード


// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;


// 変数の初期化＆配列宣言
$err_msg  = array();  // エラーメッセージ
$success_msg = ''; // 成功メッセージ
$mode='';

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // アップロードした新しいデータを取得
    try {
        // データベースに接続
        $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
         
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


?>
<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="login.css">
    <title>イラスト音楽素材 マテリアル 「ログイン」</title>
    </head>
    <body class="back-image">
        <header class="header-line logo-image">
            
        </header>
        
        <main>
            <div class="radius-border main-width text-center">
                <p class="title-text m-p-reset">ログイン</p>
                <p class="text-mini">ユーザー名とパスワードを入力してログイン</p>
                <form method="post">
                    <p><input class="seach-border" type="text" name="user_name" placeholder="ユーザー名"></p>
                    <p><input class="seach-border" type="password" name="password" placeholder="パスワード"></p>
                    <p><input class="green-text-button" type="submit" value="ログイン"></p>
                    <input type="hidden" name="mode" value="rogin">
                    <p><a class="new-text" href="register.php">新規作成</a></p>
                </form>
                <!--エラーメッセージ-->
                <div class="red-text bold-text"><?php foreach($err_msg as $value){ ?>
                <div> <?php print $value ?><br></div> 
                <?php } ?></div>
                <p class="text-color-gray text-mini">ログインしてほしい素材を手に入れましょう</p>
            </div>
        </main>
    </body>
</html>