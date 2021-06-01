<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
        <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
        <title>マテリアル 商品管理ツール</title>
        <style>
            div{
              padding: 3px 0px;  
            }
            .form-boder{
              border-bottom: solid 1px; padding-bottom: 25px;
            }
            table,tr,td,th{
              text-align: center;
            }
            .item-back-color{
                background-color: #b0b0b0;
            }
            img{
                width:100px;
            }
            
        </style>
    </head>
    <body>
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header_logined.php'; ?>

        <!--メッセージのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>
        
        <h1 style="border-bottom: solid 1px; padding-bottom: 20px;">イラスト・音楽素材 マテリアル 商品管理ツール</h1>
        <p><a href="user_data.php">ユーザー管理ページ</a></p>
        <a href="item_list.php">商品一覧ページ</a>
        <h2>新規商品追加</h2>
        
        <form method="post" class="form-boder" enctype="multipart/form-data">
            <div>名前：<input class="normal-border" type="text" name="item_name"></div>
            <div>値段：<input class="normal-border" type="number" name="price"></div>
            <div>個数：<input class="normal-border" type="number" name="stock"></div>
            <div><input type="file" name="new_file"></div>
            <div>ジャンル：
                <select  class="normal-border" name="type2">
                    <option value="アイコン">アイコン</option>
                    <option value="ヘッダー">ヘッダー</option>
                    <option value="立ち絵">立ち絵</option>
                    <option value="背景">背景</option>
                    <option value="bgm">bgm</option>
                    <option value="効果音">効果音</option>
                </select>
            </div>
            <div>公開ステータス：
                <select class="normal-border" name="release">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
            </div>
            <div>商品説明</div>
            <textarea class="comment-box" cols="40" rows="5" name="comment"></textarea>
            <input type="hidden" name="star" value="3"> <!--デフォルトの値 星3-->
            <input type="hidden" name="review_amount" value="1"> <!--デフォルトの値 レビュー回数-->
            <div><input class="red-text-button margin" type="submit" value="商品を追加"></div>
        </form>
        
        <h2>商品情報変更</h2>
        <div>商品一覧</div>
        <table border="1" cellspacing="0" cellpadding="0" width="1700">
        <tr>
            <th>商品画像</th>
            <th>商品名</th>
            <th>値段</th>
            <th>在庫数</th>
            <th>公開ステータス</th>
            <th>種類 1:イラスト 2:音楽</th>
            <th>ジャンル</th>
            <th>評価の星</th>
            <th>評価回数</th>
            <th>コメント</th>
            <th>音楽再生</th>
            <th>削除</th>
        </tr>
        
        <!-- 読み込んだ商品名、値段、画像を書き出す -->
        <?php foreach($data as $value) {?>
        <!-- 公開ステータスが非公開の商品は背景を灰色にする -->
        <tr <?php if($value['status'] === 0){ ?> class="item-back-color" <?php } ?>>
            <td><img src="<?php if( $value['type'] === 1) {print $img_dir . $value['filename'];} else if($value['type'] === 2){print $img_dir . 'noimage.png';}?>"></td>
            <td><?php print htmlspecialchars($value['name'], ENT_QUOTES,'UTF-8');?></td>
            <td><?php print $value['price'].'円'; ?></td>
            
            <td>
                <form method="post">
                    <input class="normal-border margin" type="text" name="stock_up" size="5" value="<?php print $value['stock']; ?>">個
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                    <input class="gray-button" type="submit" value="変更">
                </form>
            </td>
            <td>
                <form method="post">
                    <input class="gray-button margin" type="submit" value="<?php if($value['status'] === 1){ print "公開 → 非公開"; } else { print "非公開 → 公開";}?>">
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                    <input type="hidden" name="release"  value="<?php print $value['status']; ?>">
                </form>
            </td>
            
            <td><?php print $value['type']; ?></td>
            <td><?php print htmlspecialchars ($value['type2'], ENT_QUOTES,'UTF-8'); ?></td>
            <td><?php print $value['star']; ?></td>
            <td><?php print $value['review_amount']; ?></td>
            <td><?php print htmlspecialchars ($value['comment'], ENT_QUOTES,'UTF-8'); ?></td>
            <?php 
            $filename = $value['filename'];
            if($value['type'] === 1){
            print '<td>なし</td>';
            }?>
            <?php if($value['type'] === 2){
            print '<td><audio src=" ' ?> <?php print $bgm_dir . $filename; ?> <?php print ' "controls></audio></td>';
            }?>
            
            <td>
                <form method="post">
                    <input class="gray-button margin" type="submit" name="delete" size="5" value="削除">
                    <input type="hidden" name="item_id" value="<?php print $value['id']; ?>">
                </form>
            </td>
        </tr>
        <?php
        }
        ?>
        
        </table>
    </body>
</html>