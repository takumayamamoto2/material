<!-- 新規商品追加用のエラーに格納したものを書き出す -->
<?php foreach(get_errors() as $error){ ?>
        <div class="red-text bold-text text-big"> <?php print $error ?> </div>
<?php } 
//エラーメッセージの消去
delete_errors();
?>

<!-- 処理成功用のメッセージに格納したものを書き出す -->
<?php foreach(get_messages() as $message){ ?>
        <div class="blue-text bold-text text-big" > <?php print $message ?> </div>
<?php } 
//エラーメッセージの消去
delete_messages();
?>