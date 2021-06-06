<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「商品詳細」</title>
    </head>
    <body class="back-color-sub">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header_logined.php'; ?>
        
        <div class="back-color-nav">
            <nav class="width-900">
                <div class="text-left"><a href="item_list.php">商品一覧に戻る</a></div>
                
                
                <form method="post" action="item_list.php" class="margin-bottom">
                <?php if($type_bind == ITEM_TYPE['illust']){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="illust" value="$data[type]">'; 
                }?>
                    
                <?php if($type_bind == ITEM_TYPE['music']){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="music" value="$data[type]">'; 
                }?>
                
                <input type="hidden" name="mode" value="search">
                <input type="hidden" name="csrf_token" value="<?php print $_SESSION['csrf_token']; ?>">
                
                </form>
            </nav>
        </div>
        <main class="margin">
            <div class="flex item-set padding main-width-900">
                <div>
                <?php if($item_data['type'] == ITEM_TYPE['illust']){
                    print '<img class="img-lock-big" src=" ' ?><?php print IMG_PATH . $item_data['filename']; ?> <?php print '">';
                }?>
                <?php if($item_data['type'] == ITEM_TYPE['music']){
                    print '<img class="img-lock-big" src=" ' ?><?php print IMG_PATH . 'noimage.png'; ?> <?php print '">';
                    print '<div class="margin-top">サンプルを聞く</div>
                    <audio src="';?> <?php print BGM_PATH . $item_data['filename']; ?> <?php print '"controls></audio>';
                }?>  
                </div>
                
                <div class="margin-center">
                    <div class="title-text"><?php print $item_data['name'] ?></div>
                    <div>種類：<?php print $item_data['type2'] ?></div>
                    <div class="title-text">￥<?php print $item_data['price'] ?></div>
                    <div class="orenge-text">
                        <?php //星の数の合計÷評価回数 = 小数第一位四捨五入(結果)で星の値を出す。
                                $sum = $item_data['star']/($item_data['review_amount']);
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
                    <div><?php print $item_data['comment'] ?></div>
                    
                    <div class="flex margin-top">
                        <form method="post" action="details_add_cart.php">
                            <?php if($item_data['stock'] <= 0){ ?>
                            <dd class="red-text">売り切れました</dd>
                            <?php } else { ?>
                            <dd><input class="add-text-button padding margin" type="submit" value="カートに追加する"></dd>
                            <?php } ?>
                            <dd><input type="hidden" name="item_id" value="<?php print $item_id;?>"></dd>
                            <dd><input type="hidden" name="mode" value="add_cart"></dd>
                            <input type="hidden" name="csrf_token" value="<?php print $_SESSION['csrf_token']; ?>">
                        </form>
                        <div class="margin"><a href="cart.php" class="look-button padding link-none block">カートに入れた商品を見る</a></div>
                    </div>
                </div>
            </div>
            
            <div class="main-width-900">
                <form method="post" action="details_review.php">

                    <!--メッセージのテンプレート-->
                    <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>

                    <div class="bold-text">【この商品の評価をする】</div>

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
                    <input type="hidden" name="item_id" value="<?php print $item_id;?>">
                    <input type="hidden" name="mode" value="review">
                    <input type="hidden" name="csrf_token" value="<?php print $_SESSION['csrf_token']; ?>">
                </form>
                
                <div class="bold-text back-color-white">【ユーザーの評価】
                    <!-- 読み込んだ名前、コメントを書き出す -->
                    <ul>
                    <?php foreach($come_data as $read){ ?>
                        <li class="margin-bottom">
                            <div class="flex">
                            <?php
                            
                            $sum = $read['star'];
                            
                            print $read['user_name']."さんの評価".'　' ;?>
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
                            <?php print $read['user_comment']; ?>
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