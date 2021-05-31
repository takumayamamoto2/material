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

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="item_list.css">
    <title>イラスト音楽素材 マテリアル 「商品詳細」</title>
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
        <div class="back-color-nav">
            <nav class="width-900">
                <div class="text-left"><a href="item_list.php">商品一覧に戻る</a></div>
                <!--処理成功メッセージ-->
                <div class="blue-text bold-text text-big"><?php print $success_msg; ?></div>
                
                <form method="post" action="item_list.php" class="margin-bottom">
                <?php if($rows['type'] === 1){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="illust" value="$data[type]">'; 
                }?>
                    
                <?php if($rows['type'] === 2){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="music" value="$data[type]">'; 
                }?>
                
                <input type="hidden" name="mode" value="search">
                
                </form>
            </nav>
        </div>
        <main class="margin">
            <div class="flex item-set padding main-width-900">
                <div>
                <?php if($rows['type'] === 1){
                    print '<img class="img-lock-big" src=" ' ?><?php print $img_dir . $rows['filename']; ?> <?php print '">';
                }?>
                <?php if($rows['type'] === 2){
                    print '<img class="img-lock-big" src=" ' ?><?php print $img_dir . 'noimage.png'; ?> <?php print '">';
                    print '<div class="margin-top">サンプルを聞く</div>
                    <audio src="';?> <?php print $bgm_dir . $rows['filename']; ?> <?php print '"controls></audio>';
                }?>  
                </div>
                
                <div class="margin-center">
                    <div class="title-text"><?php print $rows['name'] ?></div>
                    <div>種類：<?php print $rows['type2'] ?></div>
                    <div class="title-text">￥<?php print $rows['price'] ?></div>
                    <div class="orenge-text">
                        <?php //星の数の合計÷評価回数 = 小数第一位四捨五入(結果)で星の値を出す。
                                $sum = $rows['star']/($rows['review_amount']);
                                $sum = round($sum);
                                
                                if($sum == 1 ){ print '★☆☆☆☆';} 
                                else if($sum == 2 ){ print '★★☆☆☆';}
                                else if($sum == 3 ){ print '★★★☆☆';}
                                else if($sum == 4 ){ print '★★★★☆';}
                                else if($sum == 5 ){ print '★★★★★';}
                                else if($sum == 0 ){ print '★★★☆☆';}
                                ?>
                    </div>
                    <div class="bold-text text-big">【商品説明】</div>
                    <div><?php print $rows['comment'] ?></div>
                    
                    <div class="flex margin-top">
                        <form method="post">
                            <?php if($rows['stock'] <= 0){ ?>
                            <dd class="red-text">売り切れました</dd>
                            <?php } else { ?>
                            <dd><input class="add-text-button padding margin" type="submit" value="カートに追加する"></dd>
                            <?php } ?>
                            <dd><input type="hidden" name="item_id" value="<?php print $rows['id'];?>"></dd>
                            <dd><input type="hidden" name="mode" value="add_cart"></dd>
                        </form>
                        <div class="margin"><a href="cart.php" class="look-button padding link-none block">カートに入れた商品を見る</a></div>
                    </div>
                </div>
            </div>
            
            <div class="main-width-900">
                <form method="post">
                    <div class="bold-text">【この商品の評価をする】</div>
                    <!-- エラーに格納したものを書き出す -->
                    <div class="red-text bold-text text-big"><?php foreach($err_msg as $value){ ?>
                    <div> <?php print $value ?><br></div> 
                    <?php } ?></div>
                    <div class="flex padding">
                        <select name="star" class="orenge-text">
                            <option value="5">★★★★★</option>
                            <option value="4">★★★★☆</option>
                            <option value="3">★★★☆☆</option>
                            <option value="2">★★☆☆☆</option>
                            <option value="1">★☆☆☆☆</option>
                        </select>
                        <div>星5段階で評価してください</div>
                        <input class="download-button set-right padding" type="submit" value="評価・コメントを送信">
                    </div>
                    <textarea class="comment-box" cols="40" rows="5" name="comment" placeholder="商品に対する評価コメントをお願いします"></textarea>
                    <input type="hidden" name="item_id" value="<?php print $rows['id'];?>">
                    <input type="hidden" name="mode" value="review">
                </form>
                
                <div class="bold-text back-color-white">【ユーザーの評価】
                    <!-- 読み込んだ名前、コメントを書き出す -->
                    <ul>
                    <?php foreach($come_data as $read){ ?>
                        <li class="margin-bottom">
                            <div class="flex">
                            <?php
                            
                            $sum = $read['star'];
                            
                            print htmlspecialchars($read['user_name'], ENT_QUOTES, 'UTF-8')."さんの評価".'　' ;?>
                            <div class="orenge-text">
                            <?php
                                if($sum == 1 ){ print '★☆☆☆☆';} 
                                else if($sum == 2 ){ print '★★☆☆☆';}
                                else if($sum == 3 ){ print '★★★☆☆';}
                                else if($sum == 4 ){ print '★★★★☆';}
                                else if($sum == 5 ){ print '★★★★★';}
                                else if($sum == 0 ){ print '★★★☆☆';}
                            ?>
                            </div>
                            <?php print '　' . $read['createdate'] ; ?>
                            </div>
                            <div class="gray-text">
                            <?php print htmlspecialchars($read['user_comment'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </li>
                    <?php 
                    }
                    ?>
                    </ul>
                </div>
            </div>
            
        </main>
        
    </body>
</html>