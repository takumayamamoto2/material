<header class="header-line logo-image">
    <ul class="header-list text-right set-right">
                
        <li class="margin"><?php print $user['user_name'];?>さん</li>
        <?php if(is_admin($user)){ ?>
            <li class="margin"><a class="link-none" href="admin.php">商品管理ページ</a></li>
        <?php } ?>
        <li class="margin"><a class="link-none" href="item_list.php">商品一覧ページ</a></li>
        <li class="margin"><a class="link-none" href="purchased.php">購入済み商品</a></li>
        <li class="margin"><a class="link-none" href="cart.php">カート</a></li>
        <li class="margin"><a class="link-none" href="logout.php">ログアウト</a></li>
            
    </ul>
</header>