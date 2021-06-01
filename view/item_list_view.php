<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「商品一覧」</title>
    </head>
    <body class="back-color-sub">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header_logined.php'; ?>
        
        <div class="back-color-nav">
            <nav class="width-900">
                <div method="post" class="margin-bottom">
                <form method="post">
                    <?php if($type_bind === 1){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="illust" value="$data[type]">'; 
                    }?>
                    
                    <?php if($type_bind === 2){
                    print'<p><input class="search-border" type="search" name="item_search" placeholder="商品を検索"><input class="normal-border padding search-image pointer" type="submit" value=" "></p>
                          <input type="hidden" name="music" value="$data[type]">'; 
                    }?>
                    <input type="hidden" name="mode" value="search">
                </form>
                    <div class="flex padding">
                        <form method="post" class="normal-border flex-para">
                            <input class="<?php if($type_bind === 1){ print 'red-text-button margin'; } else { print 'gray-text-button margin';}?>" type="submit" name="illust" value="イラスト">
                            <input class="<?php if($type_bind === 2){ print 'red-text-button margin'; } else { print 'gray-text-button margin';}?>" type="submit" name="music" value="BGM・効果音">
                            <input type="hidden" name="mode" value="search">
                        </form>
                        
                        <form method="post" class="flex-para">
                            <select name="sort" class="normal-border padding">
                            <?php if($type_bind === 1){
                                
                                  print'<option value="review" '?> <?php if($sort === 'review'){ print 'selected';} ?> <?php print '>評価順</option>'?>
                            <?php print'<option value="price" '?> <?php if($sort === 'price'){ print 'selected';} ?> <?php print '>値段順</option>'?>
                            <?php print'<option value="new" '?> <?php if($sort === 'new'){ print 'selected';} ?> <?php print '>新着順</option>'?>
                            <?php print'<option value="type" '?> <?php if($sort === 'type'){ print 'selected';}?> <?php print '>種類順</option>'?>
                            <?php print'<input type="hidden" name="illust" value="$data[type]">';
                            }?>
                            <?php if($type_bind === 2){
                                
                                  print'<option value="review" '?> <?php if($sort === 'review'){ print 'selected';} ?> <?php print '>評価順</option>'?>
                            <?php print'<option value="price" '?> <?php if($sort === 'price'){ print 'selected';} ?> <?php print '>値段順</option>'?>
                            <?php print'<option value="new" '?> <?php if($sort === 'new'){ print 'selected';} ?> <?php print '>新着順</option>'?>
                            <?php print'<option value="type" '?> <?php if($sort === 'type'){ print 'selected';} ?> <?php print '>種類順</option>'?>
                            <?php print'<input type="hidden" name="music" value="$data[type]">';
                            }?>
                            
                            </select>
                            <input class="sort-button normal-border" type="submit" value="並び替え">
                            <input type="hidden" name="mode" value="search">
                        </form>
                        
                        <form method="post" class="flex-para">
                            <select name="squeeze" class="normal-border padding">
                            <?php if($type_bind === 1){
                                  print '<option value="%" '?> <?php if($squeeze === '%'){ print 'selected';} ?> <?php print '>全て</option>'?>
                            <?php print '<option value="500" '?> <?php if($squeeze === '500'){ print 'selected';} ?> <?php print '>￥500以下</option>'?>
                            <?php print '<option value="300" '?> <?php if($squeeze === '300'){ print 'selected';} ?> <?php print '>￥300以下</option>'?>
                            <?php print '<option value="3.5" '?> <?php if($squeeze === '3.5'){ print 'selected';} ?> <?php print '>星評価4以上のみ</option>'?>
                            <?php print '<option value="0" '?> <?php if($squeeze === '0'){ print 'selected';} ?> <?php print '>￥0のみ</option>'?>
                            <?php print '<option value="%アイコン" '?> <?php if($squeeze === '%アイコン'){ print 'selected';} ?> <?php print '>アイコンのみ</option>'?>
                            <?php print '<option value="%立ち絵" '?> <?php if($squeeze === '%立ち絵'){ print 'selected';} ?> <?php print '>立ち絵のみ</option>'?>
                            <?php print '<option value="%背景" '?> <?php if($squeeze === '%背景'){ print 'selected';} ?> <?php print '>背景のみ</option>'?>
                            <?php print '<option value="%ヘッダー" '?> <?php if($squeeze === '%ヘッダー'){ print 'selected';} ?> <?php print '>ヘッダーのみ</option>'?>
                            <?php print'<input type="hidden" name="illust" value="$data[type]">';
                            }?>
                            <?php if($type_bind === 2){
                                  print '<option value="%" '?> <?php if($squeeze === '%'){ print 'selected';} ?> <?php print '>全て</option>'?>
                            <?php print '<option value="500" '?> <?php if($squeeze === '500'){ print 'selected';} ?> <?php print '>￥500以下</option>'?>
                            <?php print '<option value="300" '?> <?php if($squeeze === '300'){ print 'selected';} ?> <?php print '>￥300以下</option>'?>
                            <?php print '<option value="3.5" '?> <?php if($squeeze === '3.5'){ print 'selected';} ?> <?php print '>星評価4以上のみ</option>'?>
                            <?php print '<option value="%bgm" '?> <?php if($squeeze === '%bgm'){ print 'selected';} ?> <?php print '>BGMのみ</option>'?>
                            <?php print '<option value="%効果音" '?> <?php if($squeeze === '%効果音'){ print 'selected';} ?> <?php print '>効果音のみ</option>'?>
                            <?php print'<input type="hidden" name="music" value="$data[type]">';
                            }?>
                            </select>
                            <input class="filtering-button normal-border" type="submit" value="絞り込み">
                            <input type="hidden" name="mode" value="search">
                        </form>
                    </div>
                </div>
            </nav>
        </div>
        <main>
            <div class="width-900 back-color-main padding">

                <!--メッセージのテンプレート-->
                <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>

                <!--データベースから件数を取得-->
                <p>検索結果：
                <?php  
                foreach($data as $value){
                $search_sum += $value['search_result'];
                }
                print $search_sum;
                ?>件の該当商品</p>
                
                <dl class="flex-wrap">
                    <!--読み込んだデータをループで書き出す-->
                    <?php foreach($data as $value){ ?>
                        
                    <div class="margin">
                        <form action="details.php" method="post">
                            <?php if($type_bind === 1){
                            print '<dt><input type="image" name="submit" class="img-lock" src=" ' ?> <?php print $img_dir . $value['filename']; ?> <?php print ' "></dt>';
                            }?>
                            <?php if($type_bind === 2){
                            print '<dt><input type="image" name="submit" class="img-lock" src=" ' ?> <?php print $img_dir . 'noimage.png'; ?> <?php print ' "></dt>';
                            }?>
                            <dd class="bold-text"><?php print htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8'); ?></dd>
                            <dd>種類：<?php print htmlspecialchars($value['type2'],ENT_QUOTES,'UTF-8'); ?></dd>
                            <dd class="bold-text text-big">￥<?php print $value['price'];?></dd>
                            <dd class="orenge-text">
                                <?php //星の数の合計÷評価回数 = 小数第一位四捨五入(結果)で星の値を出す。
                                $sum = $value['star']/($value['review_amount']);
                                $sum = round($sum);
                                
                                if($sum == 1 ){ print '★☆☆☆☆';} 
                                else if($sum == 2 ){ print '★★☆☆☆';}
                                else if($sum == 3 ){ print '★★★☆☆';}
                                else if($sum == 4 ){ print '★★★★☆';}
                                else if($sum == 5 ){ print '★★★★★';}
                                else if($sum == 0 ){ print '★★★☆☆';}
                                ?>
                            </dd>
                            
                            <dd><input class="orenge-text pointer" type="submit" value="商品詳細"></dd>
                            <dd><input type="hidden" name="item_id" value="<?php print $value['id'];?>"></dd>
                            <input type="hidden" name="mode" value="item_detail">
                            
                        </form>
                        <?php if($value['stock'] <= 0){ ?>
                        <dd class="red-text">売り切れました</dd>
                        <?php } else { ?>
                        <form method="post">
                            <dd><input class="add-text-button margin-bottom-power" type="submit" value="カートに追加する"></dd>
                            <input type="hidden" name="item_id" value="<?php print $value['id'];?>">
                            <input type="hidden" name="mode" value="add_cart">
                        </form>
                        <?php } ?>
                    </div>
                    
                    <?php } ?>
                </dl>
            </div>
        </main>

        
    </body>
</html>