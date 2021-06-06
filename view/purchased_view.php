<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「購入済み一覧」</title>
    </head>
    <body class="back-color-sub">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header_logined.php'; ?>
        
        <nav>
            <p><a href="item_list.php">商品一覧に戻る</a></p>
        </nav>
        
        <!--メッセージのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>

        <main class="min-1000">
            <p class="title-text">購入済み商品</p>
            <!--購入商品がまだ無いときのメッセージ-->
            <?php 
            if(empty($item_data) !== false){
            print '<div class="red-text bold-text text-big">' ?> <?php print '商品はありません。</div>';
            } ?>
            
            <!--読み込んだデータをループで書き出す-->
            <?php foreach($item_data as $value){ ?>
            <div class="flex item-set padding height-200">
                <?php if($value['type'] == ITEM_TYPE['illust']){
                    print '<div><img class="img-lock" src=" ' ?> <?php print IMG_PATH . $value['filename']; ?> <?php print ' "></div>';
                    }?>
                    <?php if($value['type'] == ITEM_TYPE['music']){
                    print '<div><img class="img-lock" src=" ' ?> <?php print IMG_PATH . 'noimage.png'; ?> <?php print ' "></div>';
                    }?>
                <div class="width-500">
                    <div class="title-text"><?php print $value['name']; ?></div>
                    <div>種類：<?php print $value['type2']; ?></div>
                    <div class="bold-text"><?php print $value['amount_sum']; ?>個 ￥<?php print $value['amount_sum'] * $value['price_sum']; ?>で購入済み</div>
                    <div class="bold-text text-big">【商品説明】</div>
                    <div><?php print $value['comment']; ?></div>
                </div>
                
                <form action="download.php" method="post" class="set-right">
                    <div><input class="bold-text download-button padding margin" type="submit" value="ダウンロードする"></div>
                    <?php if($value['type'] == ITEM_TYPE['music']){    
                    print '<div class="margin-top">サンプルを聞く</div>
                    <audio src="';?> <?php print BGM_PATH . $value['filename']; ?> <?php print '"controls></audio>';
                }?>  
                    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php print $_SESSION['csrf_token']; ?>">
                </form>
            </div>
            <?php } ?>
            
        </main>
    </body>
</html>