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
$item_id = '';
$price_sum = '';

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    //処理モードの判別
    if(isset($_POST['mode'])){
        $mode = $_POST['mode'];
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
        //既存のアップロードされたデータの取得
        try {
            
            // SQL文を作成 material_itemsを取得
            $sql ='SELECT 
            	material_items.id,
                material_items.name,
                material_items.filename,
                material_items.type,
                material_items.type2,
                material_items.comment,
                material_item_history.item_id,
                SUM(material_items.price) AS price_sum,
                SUM(material_item_history.amount) AS amount_sum
            FROM
                material_items
                INNER JOIN material_item_history
                ON material_items.id = material_item_history.item_id
            WHERE
                user_id = ?
            GROUP BY
                material_items.id;';
                
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="item_list.css">
    <title>イラスト音楽素材 マテリアル 「購入済み一覧」</title>
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
            <p class="title-text">購入済み商品</p>
            <!--購入商品がまだ無いときのメッセージ-->
            <?php 
            if(count($data) === 0){
            print '<div class="red-text bold-text text-big">' ?> <?php print '商品はありません。</div>';
            } ?>
            
            <!--読み込んだデータをループで書き出す-->
            <?php foreach($data as $value){ ?>
            <div class="flex item-set padding height-200">
                <?php if($value['type'] === 1){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . $value['filename']; ?> <?php print ' "></div>';
                    }?>
                    <?php if($value['type'] === 2){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . 'noimage.png'; ?> <?php print ' "></div>';
                    }?>
                <div class="width-500">
                    <div class="title-text"><?php print htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div>種類：<?php print htmlspecialchars($value['type2'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div class="bold-text"><?php print $value['amount_sum']; ?>個 ￥<?php print $value['amount_sum'] * $value['price_sum']; ?>で購入済み</div>
                    <div class="bold-text text-big">【商品説明】</div>
                    <div><?php print htmlspecialchars($value['comment'],ENT_QUOTES,'UTF-8'); ?></div>
                </div>
                
                <form action="download.php" method="post" class="set-right">
                    <div><input class="bold-text download-button padding margin" type="submit" value="ダウンロードする"></div>
                    <?php if($value['type'] === 2){    
                    print '<div class="margin-top">サンプルを聞く</div>
                    <audio src="';?> <?php print $bgm_dir . $value['filename']; ?> <?php print '"controls></audio>';
                }?>  
                    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                </form>
            </div>
            <?php } ?>
            
        </main>
    </body>
</html>