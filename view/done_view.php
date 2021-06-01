<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「購入完了」</title>
    </head>
    <body class="back-color-sub">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header_logined.php'; ?>
        
        <nav>
            <p><a href="item_list.php">商品一覧に戻る</a></p>
        </nav>
        
        <main class="min-1000">
            <!--メッセージのテンプレート-->
            <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>
            
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