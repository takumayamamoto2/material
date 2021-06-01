<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
        <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'item_list.css'); ?>">
        <title>イラスト音楽素材 マテリアル「ユーザー管理」</title>
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
        
        <h1 style="border-bottom: solid 1px; padding-bottom: 20px;">イラスト・音楽素材 マテリアル ユーザー管理ツール</h1>
        <p><a class="" href="logout.php">ログアウト</a></p>
        <a href="admin.php">商品管理ページ</a>
        <h2>ユーザー情報一覧</h2>
        
        <table border="1" cellspacing="0" cellpadding="0" width="900">
        <tr>
            <th>ユーザー名</th>
            <th>登録日時</th>
        </tr>
        
        <!-- 読み込んだ名前と登録日を書き出す -->
        <?php foreach($data as $value) {?>
        <tr>
            <td><?php print htmlspecialchars($value['user_name'], ENT_QUOTES,'UTF-8');?></td>
            <td><?php print $value['createdate']; ?></td>
        </tr>
        <?php
        }
        ?>
        
        </table>
    </body>
</html>