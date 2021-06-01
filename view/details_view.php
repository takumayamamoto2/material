<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
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