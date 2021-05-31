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
$img_dir            = './img/';  //アップロードした新しい画像ファイルの保存ディレクトリ
$bgm_dir            = './bgm/';  //アップロードした新しい音楽ファイルの保存ディレクトリ
$data               = array();  // 下に商品一覧を取得して表示させるための配列
$err_msg            = array();  // エラーメッセージ
$success_msg        = array();  // 処理成功メッセージ
$new_filename   = '';   // アップロードした新しいファイル名
$number_regex       = '/^[0-9]+$/'; // 正規表現 半角数字
$filename = '';

// エラーチェック&アップロード画像ファイルの保存
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    
    // 在庫数変更用のフィルタリング、エラーチェック
    if (isset($_POST['stock_up']) === TRUE) {
        // フォームからのデータ受け取り処理　データが入っていれば$POSTの中身を変数に代入
        $sotck_up = '';
        if(isset($_POST['stock_up']) === TRUE){
            $stock_up = $_POST['stock_up'];
        }
        
        // データフィルタリング処理　全角や半角の空白を変換
        // str_replace(変換前,変換後,配列名)で文字を置換する（全角空白を半角空白に置き換え）trimで前後の空白を消す(半角空白のみ)
        $stock_up = str_replace('　',' ',$stock_up);
        $stock_up = trim($stock_up);
        
        // データのエラーチェック エラーの場合は配列にエラー文字を格納していく
        if($stock_up === ''){
            $err_msg[] = '個数を入力してください';
        }  else if(preg_match($number_regex,$stock_up) === 0){
            $err_msg[] = '個数は半角数値を入力してください';
        }
        /*else if(is_numeric($stock_up) !== TRUE){
            $err_msg_stock[] = '個数は半角数値で入力してください';
        }*/
    }
    
    // 公開ステータス変更用のエラーチェック
    if (isset($_POST['release'],$_POST['item_id']) === TRUE) {
        
        // 変数の初期化
        $status = '';
        $status = $_POST['release'];
        
        // 0かつ1以外の数値が入ってきたらエラーメッセージ
        if($status !== '0' && $status !== '1'){
            $err_msg[] = '不正な処理です';
        }
    }
    
    
    // 新規商品追加用のフィルタリング、エラーチェック
    if (isset($_POST['item_name'],$_POST['price'],$_POST['stock'],$_FILES['new_file'],$_POST['release'],$_POST['type2'],$_POST['star'],$_POST['comment'],$_POST['review_amount'])) {
        // フォームからのデータ受け取り処理　データが入っていればPOSTの中身を変数に代入
        $name = '';
        if(isset($_POST['item_name']) === TRUE){
            $name = $_POST['item_name'];
        }
        
        $price = '';
        if(isset($_POST['price']) === TRUE){
            $price = $_POST['price'];
        }
        
        $stock = '';
        if(isset($_POST['stock']) === TRUE){
            $stock = $_POST['stock'];
        }
        
        $file = '';
        if(isset($_POST['new_file']) === TRUE){
            $file = $_POST['new_file'];
        }
        
        $status = '';
        if(isset($_POST['release']) === TRUE){
            $status = $_POST['release'];
        }
        
        $type2 = '';
        if(isset($_POST['type2']) === TRUE){
            $type2 = $_POST['type2'];
        }
        
        $star = '';
        if(isset($_POST['star']) === TRUE){
            $star = $_POST['star'];
        }
        
        $review_amount = '';
        if(isset($_POST['review_amount']) === TRUE){
            $review_amount = $_POST['review_amount'];
        }
        
        $comment = '';
        if(isset($_POST['comment']) === TRUE){
            $comment = $_POST['comment'];
        }
        
        // データフィルタリング処理　全角や半角の空白を変換
        // str_replace(変換前,変換後,配列名)で文字を置換する(全角空白を半角空白に置き換え) trimで前後の空白を消す(半角空白のみ)
        $name = str_replace('　',' ',$name);
        $name = trim($name);
        $price = str_replace('　',' ',$price);
        $price = trim($price);
        $stock = str_replace('　',' ',$stock);
        $stock = trim($stock);
        $comment = str_replace('　',' ',$comment);
        $comment = trim($comment);
        
        // データのエラーチェック　エラーの場合は配列にエラー文字を格納していく
        
        // 名前の処理
        if($name === ''){
            $err_msg[] = '名前を入力してください';
        } else if(mb_strlen($name) > 100){
            $err_msg[] = '名前は100文字以内で入力してください';
        }
        
        // 金額の処理
        if($price === ''){
            $err_msg[] = '値段を入力してください';
        } else if($price > 10000){
            $err_msg[] = '値段は１万円以下にしてください';
        } else if(preg_match($number_regex,$price) === 0){
            $err_msg[] = '値段には半角数値を入力してください';
        }
        
        /*if(is_numeric($price) !== TRUE && $price !== ''){
            $err_msg[] = '値段には半角数値を入力してください';
        }*/
        
        // 個数の処理
        if($stock === ''){
            $err_msg[] = '個数を入力してください';
        } else if(preg_match($number_regex,$stock) === 0){
            $err_msg[] = '個数は半角数値を入力してください';
        }
        /*if(is_numeric($stock) !== TRUE && $stock !== ''){
            $err_msg[] = '個数には半角数値を入力してください';
        }*/
        
        // HTTP POST でファイルがアップロードされたかどうかチェック
        // is_uploaded_file関数はPOST通信でアップロードされたファイルならtrue、それ以外の方法でアップされているならfalseを返す
        if (is_uploaded_file($_FILES['new_file']['tmp_name']) !== TRUE) {
            $err_msg[] = 'ファイルを選択してください';
        }
        
        // 公開ステータス　0かつ1以外の数値が入ってきたらエラーメッセージ
        if($status !== '0' && $status !== '1'){
            $err_msg[] = '不正な処理です';
        }
        
        // 種類２
        if($type2 === ''){
            $err_msg[] = 'コメントを入力してください';
        } else if(mb_strlen($type2) >= 30){
            $err_msg[] = 'ジャンルは30文字以内で入力してください';
        }
        
        // 星の値　3以外の数値が入ってきたらエラーメッセージ
        if($star !== '3'){
            $err_msg[] = '不正な処理です';
        }
        
        // コメントの処理
        if($comment === ''){
            $err_msg[] = 'コメントを入力してください';
        } else if(mb_strlen($comment) > 1000){
            $err_msg[] = 'コメントは1000文字以内で入力してください';
        }
        
        // エラーが0なら画像登録処理
        if(count($err_msg) === 0){
            // 画像の拡張子を取得 pathinfo関数で拡張子のみを取得 第二引数を指定しないと4つの値を返すが
            // PATHINFO_EXTENSIONを第二引数に指定することで拡張子名のみを取り出す
            $extension = pathinfo($_FILES['new_file']['name'], PATHINFO_EXTENSION);
            // 指定の拡張子であるかどうかチェック
            if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'JPG' || $extension === 'JPEG' || $extension === 'PNG') {
                // 拡張子が画像だったら1を代入
                $type = '1';
                // 保存する新しいファイル名の生成（ユニークな値を設定する） sha1(ファイル名からハッシュ値を生成)
                // uniqid(現在時刻に基づいた一意なIDを取得,第二引数にtrueを置くことでより細かなID生成)
                $new_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                // 同名ファイルが存在するかどうかチェック is_file(ファイルが存在していたらtrueを返す)
                if (is_file($img_dir . $new_filename) !== TRUE) {
                    // アップロードされたファイルを指定ディレクトリに移動して保存
                    // move_uploaded_file 第1パラメータには仮ファイルのパス、第2パラメータに保存先のパスを指定。保存先のパスはディレクトリのみではなく、拡張子を含めたファイル名を指定する必要がある点に注意。
                    if (move_uploaded_file($_FILES['new_file']['tmp_name'], $img_dir . $new_filename) !== TRUE) {
                        $err_msg[] = 'ファイルアップロードに失敗しました';
                    }
                } else {
                    $err_msg[] ='ファイルアップロードに失敗しました。再度お試しください。';
                }
                
            // 画像ファイルの拡張子ではなかったら音楽ファイルの拡張子を調べ、処理をする    
            } else if($extension === 'mp3' || $extension === 'wav' || $extension === 'MP3' || $extension === 'WAV') {
                // 拡張子が音楽だったら2を代入
                $type = '2';
                // 保存する新しいファイル名の生成（ユニークな値を設定する） sha1(ファイル名からハッシュ値を生成)
                // uniqid(現在時刻に基づいた一意なIDを取得,第二引数にtrueを置くことでより細かなID生成)
                $new_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                // 同名ファイルが存在するかどうかチェック is_file(ファイルが存在していたらtrueを返す)
                if (is_file($bgm_dir . $new_filename) !== TRUE) {
                    // アップロードされたファイルを指定ディレクトリに移動して保存
                    // move_uploaded_file 第1パラメータには仮ファイルのパス、第2パラメータに保存先のパスを指定。保存先のパスはディレクトリのみではなく、拡張子を含めたファイル名を指定する必要がある点に注意。
                    if (move_uploaded_file($_FILES['new_file']['tmp_name'], $bgm_dir . $new_filename) !== TRUE) {
                        $err_msg[] = 'ファイルアップロードに失敗しました';
                    }
                } else {
                    $err_msg[] ='ファイルアップロードに失敗しました。再度お試しください。';
                }
                
            } else {
                $err_msg[] ='ファイル形式が異なります。画像ファイルはJPEGもしくはPNGのみ利用可能です。音楽ファイルはMP3もしくはWAVのみ利用可能です';
            }
        }
    }
}

// アップロードした新しい画像ファイル名,＆商品名＆金額の登録、既存の画像ファイル名＆商品名＆金額を取得
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
    // エラーがなければ、アップロードした新しい画像ファイル＆商品名＆金額&公開ステータスを登録
    if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
          if (isset($_POST['item_name'],$_POST['price'],$_POST['stock'],$_FILES['new_file'],$_POST['release'],$_POST['type2'],$_POST['star'],$_POST['comment'],$_POST['review_amount']) === TRUE) {
            
            try {
                
                //material_itemsテーブルの更新処理
                
                // バインドする値をセット
                $item_name = $_POST['item_name'];
                $item_price = $_POST['price'];
                $item_release = $_POST['release'];
                $item_stock = $_POST['stock'];
                
                // SQL文を作成
                $sql = 'INSERT INTO material_items(name,price,filename,status,stock,type,type2,star,review_amount,comment,createdate,updatedate) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),NOW());';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $item_name,         PDO::PARAM_STR);
                $stmt->bindValue(2, $item_price,        PDO::PARAM_INT);
                $stmt->bindValue(3, $new_filename,      PDO::PARAM_STR);
                $stmt->bindValue(4, $item_release,      PDO::PARAM_INT);
                $stmt->bindValue(5, $item_stock,        PDO::PARAM_INT);
                $stmt->bindValue(6, $type,              PDO::PARAM_INT);
                $stmt->bindValue(7, $type2,             PDO::PARAM_INT);
                $stmt->bindValue(8, $star,              PDO::PARAM_INT);
                $stmt->bindValue(9, $review_amount,     PDO::PARAM_INT);
                $stmt->bindValue(10, $comment,           PDO::PARAM_STR);
                
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg[] = '追加成功';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
          }     
        
        
        
        // 在庫数変更ボタンを押した時の在庫数の保存処理
          if (isset($_POST['stock_up'],$_POST['item_id']) === TRUE) {
            try {
                
                // バインドする値をセット
                $item_id = $_POST['item_id'];
                $stock_up = $_POST['stock_up'];
                // SQL文を作成
                $sql = 'UPDATE material_items SET stock = ?, createdate = ? WHERE id = ?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $stock_up,          PDO::PARAM_INT);
                $stmt->bindValue(2, date('Y-m-d H:i:s'),PDO::PARAM_STR);
                $stmt->bindValue(3, $item_id,          PDO::PARAM_INT);
                
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg[] = '在庫変更成功';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
          }     
        
        // 公開ステータスの変更ボタン処理
         if (isset($_POST['release'],$_POST['item_id']) === TRUE) {
            try {
                // バインドする値をセット
                $item_id = $_POST['item_id'];
                $status   = $_POST['release'];
                
                // 公開ステータスの値0なら1に。1なら0に。
                if ($status == 0){
                    $status++;
                } else if ($status == 1){
                    $status--;
                }
                
                // SQL文を作成
                $sql = 'UPDATE material_items SET status = ?, createdate = ? WHERE id = ?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $status,            PDO::PARAM_INT);
                $stmt->bindValue(2, date('Y-m-d H:i:s'),PDO::PARAM_STR);
                $stmt->bindValue(3, $item_id,          PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                // この処理まで来たら成功メッセージを格納する
                $success_msg[] = 'ステータス変更成功';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
         }
        
        // 削除ボタン処理
         if(isset($_POST['delete'],$_POST['item_id'])) {
            try{
                // バインドする値をセット
                $item_id = $_POST['item_id'];
                
                // SQL文を作成
                $sql = 'DELETE FROM material_items WHERE id = ?;';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $item_id,   PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                // 成功メッセージ
                $success_msg[] = '削除成功';
            }   catch (PDOException $e) {
                // 例外をスロー
                throw $e;
            }
        }
    }

        //既存のアップロードされた画像ファイル名、商品名、値段、個数、公開ステータスの取得
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
                material_items;';
                
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
        <title>マテリアル 商品管理ツール</title>
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
        
        <!-- 新規商品追加用のエラーに格納したものを書き出す -->
        <ul>
        <?php foreach($err_msg as $value){ ?>
                <li> <?php print $value ?> </li>
        <?php } ?>
        <!-- 処理成功用のメッセージに格納したものを書き出す -->
        <?php foreach($success_msg as $value){ ?>
                <li> <?php print $value ?> </li>
        <?php } ?>
        </ul>
        
        <h1 style="border-bottom: solid 1px; padding-bottom: 20px;">イラスト・音楽素材 マテリアル 商品管理ツール</h1>
        <p><a class="" href="logout.php">ログアウト</a></p>
        <a href="user_data.php">ユーザー管理ページ</a>
        <h2>新規商品追加</h2>
        
        <form method="post" class="form-boder" enctype="multipart/form-data">
            <div>名前：<input type="text" name="item_name"></div>
            <div>値段：<input type="text" name="price"></div>
            <div>個数：<input type="text" name="stock"></div>
            <div><input type="file" name="new_file"></div>
            <div>ジャンル：
                <select name="type2">
                    <option value="アイコン">アイコン</option>
                    <option value="ヘッダー">ヘッダー</option>
                    <option value="立ち絵">立ち絵</option>
                    <option value="背景">背景</option>
                    <option value="bgm">bgm</option>
                    <option value="効果音">効果音</option>
                </select>
            </div>
            <div>公開ステータス：
                <select name="release">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
            </div>
            <div>商品説明</div>
            <textarea cols="40" rows="5" name="comment"></textarea>
            <input type="hidden" name="star" value="3"> <!--デフォルトの値 星3-->
            <input type="hidden" name="review_amount" value="1"> <!--デフォルトの値 レビュー回数-->
            <div><input type="submit" value="商品を追加"></div>
        </form>
        
        <h2>商品情報変更</h2>
        <div>商品一覧</div>
        <table border="1" cellspacing="0" cellpadding="0" width="1700">
        <tr>
            <th>商品画像</th>
            <th>商品名</th>
            <th>値段</th>
            <th>在庫数</th>
            <th>公開ステータス</th>
            <th>種類 1:イラスト 2:音楽</th>
            <th>ジャンル</th>
            <th>評価の星</th>
            <th>評価回数</th>
            <th>コメント</th>
            <th>音楽再生</th>
            <th>削除</th>
        </tr>
        
        <!-- 読み込んだ商品名、値段、画像を書き出す -->
        <?php foreach($data as $value) {?>
        <!-- 公開ステータスが非公開の商品は背景を灰色にする -->
        <tr <?php if($value['status'] === 0){ ?> class="item-back-color" <?php } ?>>
            <td><img src="<?php if( $value['type'] === 1) {print $img_dir . $value['filename'];} else if($value['type'] === 2){print $img_dir . 'noimage.png';}?>"></td>
            <td><?php print htmlspecialchars($value['name'], ENT_QUOTES,'UTF-8');?></td>
            <td><?php print $value['price'].'円'; ?></td>
            
            <td>
                <form method="post">
                    <input type="text" name="stock_up" size="5" value="<?php print $value['stock']; ?>">個
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                    <input type="submit" value="変更">
                </form>
            </td>
            <td>
                <form method="post">
                    <input type="submit" value="<?php if($value['status'] === 1){ print "公開 → 非公開"; } else { print "非公開 → 公開";}?>">
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                    <input type="hidden" name="release"  value="<?php print $value['status']; ?>">
                </form>
            </td>
            
            <td><?php print $value['type']; ?></td>
            <td><?php print htmlspecialchars ($value['type2'], ENT_QUOTES,'UTF-8'); ?></td>
            <td><?php print $value['star']; ?></td>
            <td><?php print $value['review_amount']; ?></td>
            <td><?php print htmlspecialchars ($value['comment'], ENT_QUOTES,'UTF-8'); ?></td>
            <?php 
            $filename = $value['filename'];
            if($value['type'] === 1){
            print '<td>なし</td>';
            }?>
            <?php if($value['type'] === 2){
            print '<td><audio src=" ' ?> <?php print $bgm_dir . $filename; ?> <?php print ' "controls></audio></td>';
            }?>
            
            <td>
                <form method="post">
                    <input type="submit" name="delete" size="5" value="削除">
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                </form>
            </td>
        </tr>
        <?php
        }
        ?>
        
        </table>
    </body>
</html>