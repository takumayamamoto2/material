<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'function.php';

    /*
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
            set_error('個数を入力してください');
        }  else if(preg_match($number_regex,$stock_up) === 0){
            set_error('個数は半角数値を入力してください');
        }
        //else if(is_numeric($stock_up) !== TRUE){
        //    $err_msg_stock[] = '個数は半角数値で入力してください';
        //}
    }
    
    // 公開ステータス変更用のエラーチェック
    if (isset($_POST['release'],$_POST['item_id']) === TRUE) {
        
        // 変数の初期化
        $status = '';
        $status = $_POST['release'];
        
        // 0かつ1以外の数値が入ってきたらエラーメッセージ
        if($status !== '0' && $status !== '1'){
            set_error('不正な処理です');
        }
    }
    
    */

// 入力した名前とパスワードが有効かどうかチェック
function validate_user_resister($db, $name, $password){
    // 送られてきたユーザーデータを使って同じユーザーの名前を取得
    $user_data = get_user_data($db, $name);
    $is_valid_name = is_valid_name($user_data['user_name'], $name);
    $is_valid_password = is_valid_password($password);

    return $is_valid_name && $is_valid_password ;
}

// 名前の処理
function is_valid_name($name_data, $name){
    $check = true;
    if($name === ''){
        set_error('名前を入力してください');
        $check = false;
    } else if(preg_match(LOGIN_REGEX,$name) === 0){
        set_error('名前は半角英数字6文字以上でお願いします');
        $check = false;
    } else if($name_data === $name){
        set_error('既に同じ名前のユーザーが登録されています');
        $check = false;
    }
    return $check;
}

// パスワードの処理
function is_valid_password($password){
    $check = true;
    if($password === ''){
        set_error('パスワードを入力してください');
        $check = false;
    } else if(preg_match(LOGIN_REGEX,$password) === 0){
        set_error('パスワードは半角英数字6文字以上でお願いします');
        $check = false;
    } 
    return $check;
}


// レビューの星とコメントが有効かどうかチェック
function validate_user_review($star, $comment){

    $is_valid_star = is_valid_star($star);
    $is_valid_comment = is_valid_item_comment($comment);

    return $is_valid_star && $is_valid_comment ;
}

// 初期星の処理
function is_valid_star($star){
    $check = true;
    // 星の値　3以外の数値が入ってきたらエラーメッセージ
    if($star !== '1' && $star !== '2' && $star !== '3' && $star !== '4' && $star !== '5' ){
        set_error('不正な処理です');
        $check = false;
    }
    return $check;
}


// 購入前のカート情報が正しいかのチェック
function cart_valid($user_cart_items){
    $check = true;
    // データベースの情報と比較してエラーチェック
    foreach($user_cart_items as $value){
        // 在庫数があるかどうかチェック
        if($value['stock'] < $value['amount']){
            
            $lack = $value['amount'] - $value['stock'];
            set_error('申し訳ありませんが在庫切れの商品がございました。お手数ですが数量をご変更の上、お選びください。<br>' . '商品名：' . $value['name'] .'<br>' . '不足数：' . $lack . '<br>');
            $check = false;
        }
        
        // 公開か非公開かどうかチェック
        if($value['status'] == ITEM_STATUS['close']){
            set_error('非公開の商品があり購入できませんでした。お手数ですがもう一度商品一覧をお確かめください。<br>' . '商品名：' . $value['name'] .'<br>');
            $check = false;
        }
    }
    return $check;
}


// アップされる商品情報が有効かどうかチェック
function validate_item($name, $price, $file, $status, $stock, $type2, $star, $review_amount, $comment){
    $is_valid_item_name = is_valid_item_name($name);
    $is_valid_item_price = is_valid_item_price($price);
    $is_valid_item_file = is_valid_item_file($file);
    $is_valid_item_stock = is_valid_item_stock($stock);
    $is_valid_item_status = is_valid_item_status($status);
    $is_valid_item_type2 = is_valid_item_type2($type2);
    $is_valid_item_star = is_valid_item_star($star);
    $is_valid_item_review_amount = is_valid_item_review_amount($review_amount);
    $is_valid_item_comment = is_valid_item_comment($comment);

    return $is_valid_item_name 
    && $is_valid_item_price 
    && $is_valid_item_file
    && $is_valid_item_stock 
    && $is_valid_item_status
    && $is_valid_item_type2
    && $is_valid_item_star
    && $is_valid_item_review_amount
    && $is_valid_item_comment;
}


// 新規商品追加用のフィルタリング、エラーチェック
// 名前の処理
function is_valid_item_name($name){
    $check = true;
    if($name === ''){
        set_error('名前を入力してください');
        $check = false;
    } else if(mb_strlen($name) > 100){
        set_error('名前は100文字以内で入力してください');
        $check = false;
    }
    return $check;
}

// 金額の処理
function is_valid_item_price($price){
    $check = true;
    if($price === ''){
        set_error('値段を入力してください');
        $check = false;
    } else if($price > 10000){
        set_error('値段は１万円以下にしてください');
        $check = false;
    } else if(preg_match(INTEGER_REGEX,$price) === 0){
        set_error('値段には半角数値を入力してください');
        $check = false;
    }
    return $check;
}

function is_valid_item_file($file) {
    $check = true;
    // HTTP POST でファイルがアップロードされたかどうかチェック
    // is_uploaded_file関数はPOST通信でアップロードされたファイルならtrue、それ以外の方法でアップされているならfalseを返す
    if(is_uploaded_file($file['tmp_name']) === false){
        set_error('ファイルを選択してください');
        $check = false;
    }
    return $check;
}

// 個数の処理
function is_valid_item_stock($stock){
    $check = true;
    if($stock === ''){
        set_error('個数を入力してください');
        $check = false;
    } else if(preg_match(INTEGER_REGEX,$stock) === 0){
        set_error('個数は半角数値を入力してください');
        $check = false;
    }
    return $check;
}
    
// 公開ステータス　
function is_valid_item_status($status){
    $check = true;
    // 0かつ1以外の数値が入ってきたらエラーメッセージ
    if($status !== '0' && $status !== '1'){
        set_error('不正な処理です');
        $check = false;
    }
    return $check;
}


// ジャンルの処理
function is_valid_item_type2($type2){
    $check = true;
    // 種類２
    if($type2 === ''){
        set_error('ジャンルを入力してください');
    } else if(mb_strlen($type2) >= 30){
        set_error('ジャンルは30文字以内で入力してください');
        $check = false;
    }
    return $check;
}

// 初期星の処理
function is_valid_item_star($star){
    $check = true;
    // 星の値　3以外の数値が入ってきたらエラーメッセージ
    if($star !== '3'){
        set_error('不正な処理です');
        $check = false;
    }
    return $check;
}

// 初期評価回数の処理
function is_valid_item_review_amount($review_amount){
    $check = true;
    if($review_amount !== '1'){
        set_error('不正な処理です');
        $check = false;
    }
    return $check;
}

// 商品説明の処理
function is_valid_item_comment($comment){
    $check = true;
    if($comment === ''){
        set_error('コメントを入力してください');
        $check = false;
    } else if(mb_strlen($comment) > 1000){
        set_error('コメントは1000文字以内で入力してください');
        $check = false;
    }
    return $check;
}
        
        /*
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
        */
        // データのエラーチェック　エラーの場合は配列にエラー文字を格納していく

        /*if(is_numeric($price) !== TRUE && $price !== ''){
            $err_msg[] = '値段には半角数値を入力してください';
        }*/
        
        
        /*if(is_numeric($stock) !== TRUE && $stock !== ''){
            $err_msg[] = '個数には半角数値を入力してください';
        }*/

// ファイル登録処理
function file_regist() {
            $new_filename   = '';
            // 画像の拡張子を取得 pathinfo関数で拡張子のみを取得 第二引数を指定しないと4つの値を返すが
            // PATHINFO_EXTENSIONを第二引数に指定することで拡張子名のみを取り出す
            $extension = pathinfo($_FILES['new_file']['name'], PATHINFO_EXTENSION);
            // 指定の拡張子であるかどうかチェック
            if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'JPG' || $extension === 'JPEG' || $extension === 'PNG') {
                // 保存する新しいファイル名の生成（ユニークな値を設定する） sha1(ファイル名からハッシュ値を生成)
                // uniqid(現在時刻に基づいた一意なIDを取得,第二引数にtrueを置くことでより細かなID生成)
                $new_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                // 同名ファイルが存在するかどうかチェック is_file(ファイルが存在していたらtrueを返す)
                if (is_file(IMG_DIR . $new_filename) !== TRUE) {
                    // アップロードされたファイルを指定ディレクトリに移動して保存
                    // move_uploaded_file 第1パラメータには仮ファイルのパス、第2パラメータに保存先のパスを指定。保存先のパスはディレクトリのみではなく、拡張子を含めたファイル名を指定する必要がある点に注意。
                    if (move_uploaded_file($_FILES['new_file']['tmp_name'], IMG_DIR . $new_filename) !== TRUE) {
                        set_error('ファイルアップロードに失敗しました');
                    } else {
                        return $new_filename;
                    }

                } else {
                    set_error('ファイルアップロードに失敗しました。再度お試しください。');
                }
                
            // 画像ファイルの拡張子ではなかったら音楽ファイルの拡張子を調べ、処理をする    
            } else if($extension === 'mp3' || $extension === 'wav' || $extension === 'MP3' || $extension === 'WAV') {
                // 保存する新しいファイル名の生成（ユニークな値を設定する） sha1(ファイル名からハッシュ値を生成)
                // uniqid(現在時刻に基づいた一意なIDを取得,第二引数にtrueを置くことでより細かなID生成)
                $new_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                // 同名ファイルが存在するかどうかチェック is_file(ファイルが存在していたらtrueを返す)
                if (is_file(BGM_DIR . $new_filename) !== TRUE) {
                    // アップロードされたファイルを指定ディレクトリに移動して保存
                    // move_uploaded_file 第1パラメータには仮ファイルのパス、第2パラメータに保存先のパスを指定。保存先のパスはディレクトリのみではなく、拡張子を含めたファイル名を指定する必要がある点に注意。
                    if (move_uploaded_file($_FILES['new_file']['tmp_name'], BGM_DIR . $new_filename) !== TRUE) {
                        set_error('ファイルアップロードに失敗しました');
                    } else {
                        return $new_filename;
                    }
                } else {
                    set_error('ファイルアップロードに失敗しました。再度お試しください。');
                }
                
            } else {
                set_error('ファイル形式が異なります。画像ファイルはJPEGもしくはPNGのみ利用可能です。音楽ファイルはMP3もしくはWAVのみ利用可能です');
            }
}

// 拡張子をチェックして、画像か音データか判断する
function item_type_check($filename){
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    // 指定の拡張子であるかどうかチェック
    if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'JPG' || $extension === 'JPEG' || $extension === 'PNG') {
        return ITEM_TYPE['illust'];
    } else if($extension === 'mp3' || $extension === 'wav' || $extension === 'MP3' || $extension === 'WAV') {
        return ITEM_TYPE['music'];
    } else {
        set_error('対応していない拡張子です');
    }
}
    