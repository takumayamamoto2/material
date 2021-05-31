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
$img_dir  = './img/';  //アップロードした新しい画像ファイルの保存ディレクトリ
$bgm_dir  = './bgm/';  //アップロードした新しい音楽ファイルの保存ディレクトリ
$data     = array();  // 下に商品一覧を取得して表示させるための配列
$err_msg  = array();  // エラーメッセージ
$success_msg = ''; // 成功メッセージ
$type_bind = 1; // バインドの初期値（イラスト）
$number_regex       = '/^[0-9]+$/'; // 正規表現 半角数字
$mode='';
$user_name = '';

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
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        //処理モードの判別
        if(isset($_POST['mode'])){
            $mode = $_POST['mode'];
        }
        //カート挿入モード
        if($mode ==='done'){ 
            // お買い上げ商品を表示するための取得
            try {
                
                // SQL文を作成 material_itemsを取得
                $sql ='SELECT 
                	material_items.id,
                    material_items.name,
                    material_items.price,
                    material_items.stock,
                    material_items.filename,
                    material_items.type,
                    material_items.type2,
                    material_items.comment,
                    material_items.status,
                    material_carts.item_id,
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
            
            // データベースの情報と比較してエラーチェック
            foreach($data as $value){
                // 在庫数があるかどうかチェック
                if($value['stock'] < $value['amount']){
                    
                    $lack = $value['amount'] - $value['stock'];
                    $err_msg[] = '申し訳ありませんが在庫切れの商品がございました。お手数ですが数量をご変更の上、お選びください。<br>' . '商品名：' . $value['name'] .'<br>' . '不足数：' . $lack . '<br>';
                }
                
                // 公開か非公開かどうかチェック
                if($value['status'] === 0){
                    $err_msg[] = '非公開の商品があり購入できませんでした。お手数ですがもう一度商品一覧をお確かめください。<br>' . '商品名：' . $value['name'] .'<br>';
                }
            }
            
            // エラーが０なら現在庫数マイナス処理とカートテーブル削除処理
            if(count($err_msg) === 0){
                // トランザクションを開始
                $dbh->beginTransaction();
                try {
                    // ループで現在庫数からカートテーブルの数量で引いていく
                    foreach($data as $value){
                        //現在庫を取得する 商品IDと在庫数のみ
                        $sql='UPDATE material_items SET stock = stock - ? WHERE id = ?;';
                        // SQL文を実行する準備
                        $stmt = $dbh->prepare($sql);
                        // バインドする値をセット
                        $stmt->bindValue(1,$value['amount'],    PDO::PARAM_INT);
                        $stmt->bindValue(2,$value['item_id'],   PDO::PARAM_INT);
                        
                        // SQLを実行
                        $stmt->execute();
                    }
                    
                    // 購入履歴テーブルに保存する処理
                    // SQL文を作成 ループで１アイテム毎に記録していく
                    foreach($data as $value){
                    $sql = 'INSERT INTO material_item_history (user_name, user_id, item_id, amount, createdate) VALUES (?,?,?,?,NOW())';
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    // SQL文のプレースホルダに値をバインド
                    $stmt->bindValue(1,$user_name,    PDO::PARAM_STR);
                    $stmt->bindValue(2,$user_id,      PDO::PARAM_STR);
                    $stmt->bindValue(3,$value['item_id'],      PDO::PARAM_INT);
                    $stmt->bindValue(4,$value['amount'],       PDO::PARAM_INT);
                    // SQLを実行
                    $stmt->execute();
                    }
                    
                    // 購入ユーザーのカートテーブル削除処理                    
                    // SQL文を作成
                    $sql = 'DELETE FROM material_carts WHERE user_id = ?;';
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    // SQL文のプレースホルダに値をバインド
                    $stmt->bindValue(1, $user_id,   PDO::PARAM_INT);
                    // SQLを実行
                    $stmt->execute();
                    // コミット
                    $dbh->commit();
                    // 成功メッセージ
                    $success_msg = 'お買い上げありがとうございました！';
                }   catch (PDOException $e) {
                    // トランザクションロールバック
                    $dbh->rollback();
                    // 例外をスロー
                    throw $e;
                }
            }
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="item_list.css">
    <title>イラスト音楽素材 マテリアル 「購入完了」</title>
    </head>
    <body class="back-color-sub">
        <header class="header-line logo-image">
            <ul class="header-list text-right set-right">
                
            <li class="margin"><?php print $user_name;?>さん</li>
            <li class="margin"><a class="link-none" href="purchased.php">購入済み商品</a></li>
            <li class="margin"><a class="link-none" href="cart.php">カート</a></li>
            <li class="margin"><a class="link-none" href="logout.php">ログアウト</a></li>
            
            </ul>
        </header>
        
        <nav>
            <p><a href="item_list.php">商品一覧に戻る</a></p>
        </nav>
        
        <main class="min-1000">
            <!--エラーメッセージ-->
            <div class="red-text bold-text text-big"><?php foreach($err_msg as $value){ ?>
            <div> <?php print $value ?><br></div> 
            <?php } ?></div>
            
            <!--処理成功メッセージ-->
            <div class="title-text"><?php print $success_msg; ?></div>
            
             <!--直接このページにアクセスしたときのメッセージ-->
            <?php if(isset($sum['amount_sum']) === FALSE){ ?>
            <div class="red-text bold-text text-big"> <?php print 'お買い上げの商品はありません。'; ?>
            <?php } ?>
                
            <div class="header-list text-right set-right title-text">
                <div class="margin-right">数量</div>
                <div class="margin-right">値段</div>
            </div>
            
            <!--読み込んだデータをループで書き出す-->
            <?php foreach($data as $value){ ?>
            <div class="flex item-set padding">
                <?php if($value['type'] === 1){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . $value['filename']; ?> <?php print ' "></div>';
                    }?>
                    <?php if($value['type'] === 2){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . 'noimage.png'; ?> <?php print ' "></div>';
                    }?>
                <div class="width-500">
                    <div class="bold-text"><?php print htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div>種類：<?php print htmlspecialchars($value['type2'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div class="bold-text text-big">商品説明</div>
                    <div><?php print htmlspecialchars($value['comment'],ENT_QUOTES,'UTF-8'); ?></div>
                </div>
                
                <form method="post" class="header-list text-right set-right">
                    <div class="bold-text text-big margin-right"><?php print $value['amount'];?></div
                    <input type="hidden" name="id" value="<?php print $value['id'];?>">
                    <div class="bold-text text-big margin-right">￥<?php print $value['price'];?></div>
                    
                </form>
            </div>
            <?php } ?>
            
        </main>
        
        <div class="width-500 text-right set-right min-1000 padding">
            <form action="done.php" method="post">
                <div class="title-text">数量計 <?php if(isset($sum['amount_sum']) === TRUE){print $sum['amount_sum'];} else { print 0; };?>個　合計￥<?php if(isset($sum['price_sum']) === TRUE){print $sum['price_sum'];} else { print 0; }?></div>
            </form>
        </div>
    </body>
</html>