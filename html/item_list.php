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
$img_dir  = './img/';  //アップロードした新しい画像ファイルの保存ディレクトリ
$bgm_dir  = './bgm/';  //アップロードした新しい音楽ファイルの保存ディレクトリ
$data     = array();  // 下に商品一覧を取得して表示させるための配列
$data_sort  = array();  // 下に商品一覧を取得して表示させるための配列
$name_data  = '';  // ユーザー名の取得用
$err_msg  = array();  // エラーメッセージ
$success_msg = ''; // 成功メッセージ
$type_bind = ''; // バインドの初期値）
$amount = 0;    //バインドする値をセット ユーザーが初めてその商品をクリックしたときの値
$mode=''; // フォームから送られてきたものを判別するためのモード
$item_id = '';
$sort = '';
$squeeze = '';
$search = '';
$search_sum = 0;


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
    // エラーがなければ、POST処理
    if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        //処理モードの判別
        if(isset($_POST['mode'])){
            $mode = $_POST['mode'];
        }
        //カート挿入か検索モード
        if($mode ==='add_cart'){
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

        }elseif($mode === 'search'){
             // フォームからのデータ受け取り処理　データが入っていれば$POSTの中身を変数に代入
            $illust = '';
            if(isset($_POST['illust']) === TRUE){
                $illust = 1;
            }
            
            $music = '';
            if(isset($_POST['music']) === TRUE){
                $music = 2;
            }
             // 送られてきた値でバインドする値を分岐（イラストボタンまたはBGM・効果音ボタンが押されたかどうか）
            if ($illust === 1){
                $type_bind = 1;
            } else if ($music === 2){
                $type_bind = 2;
            }
            
            // 並び替え用ポストの値
            $sort = '';
            if(isset($_POST['sort']) === TRUE){
                $sort = $_POST['sort'];
            }
            
            // 並び替え用ポストの値
            $squeeze = '';
            if(isset($_POST['squeeze']) === TRUE){
                $squeeze = $_POST['squeeze'];
            }
            
            // 並び替え用ポストの値
            $search = '';
            if(isset($_POST['item_search']) === TRUE){
                $search = $_POST['item_search'];
            }
            // データフィルタリング処理　全角や半角の空白を変換
            // str_replace(変換前,変換後,配列名)で文字を置換する(全角空白を半角空白に置き換え) trimで前後の空白を消す(半角空白のみ)
            $search = str_replace('　',' ',$search);
            $search = trim($search);
        }

    }
        //検索、並び替え、絞り込み用のSQL分岐 レコードの件数も同時取得
        try {   
                // テキスト検索用
                if($search !== ''){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND material_items.type2 LIKE ? GROUP BY material_items.id';
                // 並び替え検索用
                // 並び替え「評価」順が押されたらSQLを変える
                } else if($sort === 'review'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? GROUP BY material_items.id ORDER BY star/review_amount DESC';
                // 並び替え「値段」順が押されたらSQLを変える
                } else if($sort === 'price'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? GROUP BY material_items.id ORDER BY price DESC';
                // 並び替え「更新」順が押されたらSQLを変える
                } else if($sort === 'new'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? GROUP BY material_items.id ORDER BY createdate DESC';
                // 並び替え「種類」順が押されたらSQLを変える    
                } else if($sort === 'type'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? GROUP BY material_items.id ORDER BY type2 DESC';
                // 絞り込み検索用
                // 絞り込み「￥星評価4以上のみ」が押されたらSQLを変える(評価合計(星)/評価回数が3.5以上のもの)
                } else if($squeeze === '3.5'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND (star/review_amount) >= ? GROUP BY material_items.id';
                // 絞り込み「￥0のみ」が押されたらSQLを変える
                } else if($squeeze === '0'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND material_items.price = ? GROUP BY material_items.id';
                // 絞り込み「￥300のみ」が押されたらSQLを変える
                } else if($squeeze === '300'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND material_items.price <= ? GROUP BY material_items.id';
                // 絞り込み「￥500のみ」が押されたらSQLを変える
                } else if($squeeze === '500'){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND material_items.price <= ? GROUP BY material_items.id';
                // 絞り込みのあいまい検索が出来るものが押されたらSQLを変える
                } else if(isset($squeeze) === TRUE){
                    $sql='SELECT *, COUNT(id) AS search_result FROM material_items WHERE material_items.status = 1 AND material_items.type = ? AND material_items.type2 LIKE ? GROUP BY material_items.id';
                    
                // デフォルトの並び    
                } else if($sort === ''){ 
                // SQL文を作成 material_itemsを取得
                $sql ='SELECT COUNT(id) AS search_result,
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
                    material_items.status = 1 AND
                    material_items.type = ? AND
                    material_items.type2 LIKE ?
                    GROUP BY material_items.id;';
                }
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダに値をバインド
            
            // ボタンを押していない時は初期値を差し込む
            if($type_bind === ''){ $type_bind = 1; }
            $stmt->bindValue(1, $type_bind,  PDO::PARAM_INT);
            if($squeeze === ''){ $squeeze = '%'; }
            $stmt->bindValue(2, $squeeze,  PDO::PARAM_STR);
            // 検索窓の検索が押されたらこのバインド
            if($search !== ''){
                if($search === ''){
                $search = '%';
                }
                $stmt->bindValue(2,$search.'%',  PDO::PARAM_STR);
            }
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
    <title>イラスト音楽素材 マテリアル 「商品一覧」</title>
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
        <!-- エラーに格納したものを書き出す -->
        <div class="red-text bold-text text-big"><?php foreach($err_msg as $value){ ?>
        <div> <?php print $value ?><br></div> 
        <?php } ?></div>
        
        <div class="back-color-nav">
            <nav class="width-900">
                <div method="post" class="margin-bottom">
                <form method="post">
                    <?php if($type_bind === 1){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="illust" value="$data[type]">'; 
                    }?>
                    
                    <?php if($type_bind === 2){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="music" value="$data[type]">'; 
                    }?>
                    <input type="hidden" name="mode" value="search">
                </form>
                    <div class="flex padding">
                        <form method="post" class="normal-border flex-para">
                            <input class="<?php if($type_bind === 1){ print 'red-text-button margin'; } else { print 'gray-text-button margin';}?>" type="submit" name="illust" value="イラスト">
                            <input class="<?php if($type_bind === 2){ print 'red-text-button margin'; } else { print 'gray-text-button margin';}?>" type="submit" name="music" value="BGM・効果音">
                            <input type="hidden" name="mode" value="search">
                        </form>
                        
                        <form method="post" class="flex-para">
                            <select name="sort" class="normal-border padding">
                            <?php if($type_bind === 1){
                                
                                  print'<option value="review" '?> <?php if($sort === 'review'){ print 'selected';} ?> <?php print '>評価順</option>'?>
                            <?php print'<option value="price" '?> <?php if($sort === 'price'){ print 'selected';} ?> <?php print '>値段順</option>'?>
                            <?php print'<option value="new" '?> <?php if($sort === 'new'){ print 'selected';} ?> <?php print '>新着順</option>'?>
                            <?php print'<option value="type" '?> <?php if($sort === 'type'){ print 'selected';}?> <?php print '>種類順</option>'?>
                            <?php print'<input type="hidden" name="illust" value="$data[type]">';
                            }?>
                            <?php if($type_bind === 2){
                                
                                  print'<option value="review" '?> <?php if($sort === 'review'){ print 'selected';} ?> <?php print '>評価順</option>'?>
                            <?php print'<option value="price" '?> <?php if($sort === 'price'){ print 'selected';} ?> <?php print '>値段順</option>'?>
                            <?php print'<option value="new" '?> <?php if($sort === 'new'){ print 'selected';} ?> <?php print '>新着順</option>'?>
                            <?php print'<option value="type" '?> <?php if($sort === 'type'){ print 'selected';} ?> <?php print '>種類順</option>'?>
                            <?php print'<input type="hidden" name="music" value="$data[type]">';
                            }?>
                            
                            </select>
                            <input class="sort-button normal-border" type="submit" value="並び替え">
                            <input type="hidden" name="mode" value="search">
                        </form>
                        
                        <form method="post" class="flex-para">
                            <select name="squeeze" class="normal-border padding">
                            <?php if($type_bind === 1){
                                  print '<option value="%" '?> <?php if($squeeze === '%'){ print 'selected';} ?> <?php print '>全て</option>'?>
                            <?php print '<option value="500" '?> <?php if($squeeze === '500'){ print 'selected';} ?> <?php print '>￥500以下</option>'?>
                            <?php print '<option value="300" '?> <?php if($squeeze === '300'){ print 'selected';} ?> <?php print '>￥300以下</option>'?>
                            <?php print '<option value="3.5" '?> <?php if($squeeze === '3.5'){ print 'selected';} ?> <?php print '>星評価4以上のみ</option>'?>
                            <?php print '<option value="0" '?> <?php if($squeeze === '0'){ print 'selected';} ?> <?php print '>￥0のみ</option>'?>
                            <?php print '<option value="%アイコン" '?> <?php if($squeeze === '%アイコン'){ print 'selected';} ?> <?php print '>アイコンのみ</option>'?>
                            <?php print '<option value="%立ち絵" '?> <?php if($squeeze === '%立ち絵'){ print 'selected';} ?> <?php print '>立ち絵のみ</option>'?>
                            <?php print '<option value="%背景" '?> <?php if($squeeze === '%背景'){ print 'selected';} ?> <?php print '>背景のみ</option>'?>
                            <?php print '<option value="%ヘッダー" '?> <?php if($squeeze === '%ヘッダー'){ print 'selected';} ?> <?php print '>ヘッダーのみ</option>'?>
                            <?php print'<input type="hidden" name="illust" value="$data[type]">';
                            }?>
                            <?php if($type_bind === 2){
                                  print '<option value="%" '?> <?php if($squeeze === '%'){ print 'selected';} ?> <?php print '>全て</option>'?>
                            <?php print '<option value="500" '?> <?php if($squeeze === '500'){ print 'selected';} ?> <?php print '>￥500以下</option>'?>
                            <?php print '<option value="300" '?> <?php if($squeeze === '300'){ print 'selected';} ?> <?php print '>￥300以下</option>'?>
                            <?php print '<option value="3.5" '?> <?php if($squeeze === '3.5'){ print 'selected';} ?> <?php print '>星評価4以上のみ</option>'?>
                            <?php print '<option value="%bgm" '?> <?php if($squeeze === '%bgm'){ print 'selected';} ?> <?php print '>BGMのみ</option>'?>
                            <?php print '<option value="%効果音" '?> <?php if($squeeze === '%効果音'){ print 'selected';} ?> <?php print '>効果音のみ</option>'?>
                            <?php print'<input type="hidden" name="music" value="$data[type]">';
                            }?>
                            </select>
                            <input class="filtering-button normal-border" type="submit" value="絞り込み">
                            <input type="hidden" name="mode" value="search">
                        </form>
                    </div>
                </div>
            </nav>
        </div>
        <main>
            <div class="width-900 back-color-main padding">
                <!--処理成功メッセージ-->
                <div class="green-text bold-text text-big"><?php print $success_msg; ?></div>
                <!--データベースから件数を取得-->
                <p>検索結果：
                <?php  
                foreach($data as $value){
                $search_sum += $value['search_result'];
                }
                print $search_sum;
                ?>件の該当商品</p>
                
                <dl class="flex-wrap">
                    <!--読み込んだデータをループで書き出す-->
                    <?php foreach($data as $value){ ?>
                        
                    <div class="margin">
                        <form action="details.php" method="post">
                            <?php if($type_bind === 1){
                            print '<dt><input type="image" name="submit" class="img-lock" src=" ' ?> <?php print $img_dir . $value['filename']; ?> <?php print ' "></dt>';
                            }?>
                            <?php if($type_bind === 2){
                            print '<dt><input type="image" name="submit" class="img-lock" src=" ' ?> <?php print $img_dir . 'noimage.png'; ?> <?php print ' "></dt>';
                            }?>
                            <dd class="bold-text"><?php print htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8'); ?></dd>
                            <dd>種類：<?php print htmlspecialchars($value['type2'],ENT_QUOTES,'UTF-8'); ?></dd>
                            <dd class="bold-text text-big">￥<?php print $value['price'];?></dd>
                            <dd class="orenge-text">
                                <?php //星の数の合計÷評価回数 = 小数第一位四捨五入(結果)で星の値を出す。
                                $sum = $value['star']/($value['review_amount']);
                                $sum = round($sum);
                                
                                if($sum == 1 ){ print '★☆☆☆☆';} 
                                else if($sum == 2 ){ print '★★☆☆☆';}
                                else if($sum == 3 ){ print '★★★☆☆';}
                                else if($sum == 4 ){ print '★★★★☆';}
                                else if($sum == 5 ){ print '★★★★★';}
                                else if($sum == 0 ){ print '★★★☆☆';}
                                ?>
                            </dd>
                            
                            <dd><input class="orenge-text pointer" type="submit" value="商品詳細"></dd>
                            <dd><input type="hidden" name="item_id" value="<?php print $value['id'];?>"></dd>
                            <input type="hidden" name="mode" value="item_detail">
                            
                        </form>
                        <?php if($value['stock'] <= 0){ ?>
                        <dd class="red-text">売り切れました</dd>
                        <?php } else { ?>
                        <form method="post">
                            <dd><input class="add-text-button margin-bottom-power" type="submit" value="カートに追加する"></dd>
                            <input type="hidden" name="item_id" value="<?php print $value['id'];?>">
                            <input type="hidden" name="mode" value="add_cart">
                        </form>
                        <?php } ?>
                    </div>
                    
                    <?php } ?>
                </dl>
            </div>
        </main>

        
    </body>
</html>