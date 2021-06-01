<!-- 新規商品追加用のエラーに格納したものを書き出す -->
<?php foreach($err_msg as $value){ ?>
        <div class="red-text bold-text text-big"> <?php print $value ?> </div>
<?php } ?>
<!-- 処理成功用のメッセージに格納したものを書き出す -->
<?php foreach($success_msg as $value){ ?>
        <div class="blue-text bold-text text-big" > <?php print $value ?> </div>
<?php } ?>