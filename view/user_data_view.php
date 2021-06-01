<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
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
        
        <!-- エラーに格納したものを書き出す -->
        <ul>
        <?php foreach($err_msg as $value){ ?>
                <li> <?php print $value ?> </li>
        <?php } ?>
        </ul>
        
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