<?php

$user_id = '';
// セッション開始
session_start();
// セッション変数からuser_idを取得
if($_SESSION['user_id'] === 1){
    $user_id = $_SESSION['user_id'];
} else {
// 非ログインの場合はログインページへリダイレクト
    header('Location: login.php');
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
$data               = array();  // 下に一覧を取得して表示させるための配列
$err_msg            = array();  // エラーメッセージ

// ユーザーIDとパスワードを取得
try {
        // データベースに接続
        $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
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
    
        try {
            // SQL文を作成 material_usersを取得
            $sql ='SELECT
                material_users.user_name,
                material_users.createdate
            FROM
                material_users;';
                
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                $data[] = $row;
            }
        }   catch (PDOException $e) {
            throw $e;
        }
    } catch(PDOException $e){
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー: '.$e->getMessage();
    }  
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>イラスト音楽素材 マテリアル「ユーザー管理」</title>
        <style>
            div{
              padding: 3px 0px;  
            }
            .form-boder{
              border-bottom: solid 1px; padding-bottom: 25px;
            }
            table,tr,td,th{
              text-align: center;
            }
            .item-back-color{
                background-color: #b0b0b0;
            }
            img{
                width:100px;
            }
            
        </style>
    </head>
    <body>
        
        <!-- エラーに格納したものを書き出す -->
        <ul>
        <?php foreach($err_msg as $value){ ?>
                <li> <?php print $value ?> </li>
        <?php } ?>
        </ul>
        
        <h1 style="border-bottom: solid 1px; padding-bottom: 20px;">イラスト・音楽素材 マテリアル ユーザー管理ツール</h1>
        <p><a class="" href="logout.php">ログアウト</a></p>
        <a href="admin.php">商品管理ページ</a>
        <h2>ユーザー情報一覧</h2>
        
        <table border="1" cellspacing="0" cellpadding="0" width="900">
        <tr>
            <th>ユーザー名</th>
            <th>登録日時</th>
        </tr>
        
        <!-- 読み込んだ名前と登録日を書き出す -->
        <?php foreach($data as $value) {?>
        <tr>
            <td><?php print htmlspecialchars($value['user_name'], ENT_QUOTES,'UTF-8');?></td>
            <td><?php print $value['createdate']; ?></td>
        </tr>
        <?php
        }
        ?>
        
        </table>
    </body>
</html>