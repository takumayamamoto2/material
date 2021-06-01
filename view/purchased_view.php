<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
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