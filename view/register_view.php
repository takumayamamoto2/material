<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'login.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「ユーザー登録」</title>
    </head>
    <body class="back-image">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header.php'; ?>
        
        <main>
            <div class="radius-border main-width text-center">
                <p class="title-text m-p-reset">新規ユーザー登録</p>
                <p class="margin-bottom text-mini">新しいユーザー名とパスワードを入力する</p>
                <form method="post" action = "register_process.php">
                    <label for="user_id" class="m-p-reset">ユーザー名</label>
                    <p class="margin-bottom"><input id="user_id" class="seach-border" type="text" name="user_name" placeholder="ユーザー名"></p>
                    <label for="password" class="m-p-reset">パスワード</label>
                    <p class="margin-bottom"><input id="password" class="seach-border" type="password" name="password" placeholder="パスワード"></p>
                    <p><input type="submit" class="green-text-button" value="新規登録する"></p>
                </form>
                
                <!--メッセージのテンプレート-->
                <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>

                <div><a href="login.php">ログインページに戻る</a></div>
            </div>
        </main>
    </body>
</html>