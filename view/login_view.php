<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/4.1.0/sanitize.min.css">
    <link rel="stylesheet" href="<?php print (STYLESHEET_PATH . 'login.css'); ?>">
    <title>イラスト音楽素材 マテリアル 「ログイン」</title>
    </head>
    <body class="back-image">
        <!--ヘッダーのテンプレート-->
        <?php include_once VIEW_PATH . 'templates/header.php'; ?>
        
        <main>
            <div class="radius-border main-width text-center">
                <p class="title-text m-p-reset">ログイン</p>
                <p class="text-mini">ユーザー名とパスワードを入力してログイン</p>
                <form method="post" action="login_process.php">
                    <p><input class="seach-border" type="text" name="user_name" placeholder="ユーザー名"></p>
                    <p><input class="seach-border" type="password" name="password" placeholder="パスワード"></p>
                    <p><input class="green-text-button" type="submit" value="ログイン"></p>
                    <input type="hidden" name="mode" value="rogin">
                    <p><a class="new-text" href="register.php">新規作成</a></p>
                </form>
                
                <!--メッセージのテンプレート-->
                <?php include_once VIEW_PATH . 'templates/messeage.php'; ?>

                <p class="text-color-gray text-mini">ログインしてほしい素材を手に入れましょう</p>
            </div>
        </main>
    </body>
</html>