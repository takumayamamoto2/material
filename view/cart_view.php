<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
    <title>イラスト音楽素材 マテリアル「カート」</title>
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
            <p class="title-text">ショッピングカート</p>
            <!--エラーメッセージ-->
            <div class="red-text bold-text text-big"><?php foreach($err_msg as $value){ ?>
            <div> <?php print $value ?><br></div> 
            <?php } ?></div>
            
            <!--処理成功メッセージ-->
            <div class="green-text bold-text text-big"><?php print $success_msg; ?></div>
            
             <!--商品がカートに無いときのメッセージ-->
            <?php if(isset($sum['amount_sum']) === FALSE){ ?>
            <div class="red-text bold-text text-big"> <?php print '商品はありません。'; ?></div>
            <?php } ?>
                
            <div class="header-list text-right set-right title-text">
                <div class="margin-right">数量</div>
                <div class="margin-right">値段</div>
            </div>
            
            <!--読み込んだデータをループで書き出す-->
            <?php foreach($data as $value){ ?>
            <div class="flex item-set padding min-1000">
                <?php if($value['type'] === 1){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . $value['filename']; ?> <?php print ' "></div>';
                    }?>
                    <?php if($value['type'] === 2){
                    print '<div><img class="img-lock" src=" ' ?> <?php print $img_dir . 'noimage.png'; ?> <?php print ' "></div>';
                    }?>
                <div class="width-500">
                    <div class="bold-text"><?php print htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div>種類：<?php print htmlspecialchars($value['type2'],ENT_QUOTES,'UTF-8'); ?></div>
                    <div class="bold-text text-big">【商品説明】</div>
                    <div><?php print htmlspecialchars($value['comment'],ENT_QUOTES,'UTF-8'); ?></div>
                </div>
                
                <div class="width-900-only flex-end text-right set-right width-500">
                    <form method="post" class="text-right set-right flex-end">
                        <input class="normal-border" type="text" name="amount_change" value="<?php print $value['amount'];?>">
                        <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                        <input class="gray-button" type="submit"  value="数量変更">
                        <input type="hidden" name="mode" value="amount_change">
                    </form>
                    
                    <form method="post" class="text-right set-right flex-end">    
                        <div class="bold-text text-big">￥<?php print $value['price'];?></div>
                        <input class="gray-button" type="submit" value="削除">
                        <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                        <input type="hidden" name="mode" value="delete">
                    </form>

                </div>
            </div>
            <?php } ?>
            
        </main>
        
        <div class="width-500 text-right set-right min-1000 padding">
            <form action="done.php" method="post">
                <div class="title-text">数量計 <?php if(isset($sum['amount_sum']) === TRUE){print $sum['amount_sum'];} else { print 0; };?>個　合計￥<?php if(isset($sum['price_sum']) === TRUE){print $sum['price_sum'];} else { print 0; }?></div>
                <!-- カートに商品がなければ購入ボタンは非表示 -->
                <?php if(count($data) !== 0){ print '<input class="orenge-text-button" type="submit" value="購入">'; }?>
                <input type="hidden" name="mode" value="done">
            </form>
        </div>
    </body>
</html>