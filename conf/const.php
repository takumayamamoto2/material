<?php

// データベースの初期設定
define('DB_HOST', 'mysql');
define('DB_NAME', 'material');
define('DB_USER', 'metallic');
define('DB_PASS', 'perfectcell2');
define('DB_CHARSET', 'utf8');

define('IMG_PATH', './assets/img/');
define('IMG_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/img/');
define('BGM_PATH', './assets/bgm/');
define('BGM_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/bgm/');

define('VIEW_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../view/');
define('MODEL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../model/');
define('STYLESHEET_PATH', '/assets/css/');

define('ADMIN_URL', '/admin.php');
define('HOME_URL', '/item_list.php');
define('LOGIN_URL', '/login.php');
define('REGIST_URL', '/register.php');
define('DETAILS_URL', '/details.php');
define('CART_URL', '/cart.php');
define('PURCHASE_URL', '/purchased.php');


define('USER_NUMBER_ADMIN', 1);
define('USER_NUMBER_NORMAL', 2);

define('LOGIN_REGEX', '/^[a-zA-Z0-9]{6,}$/');
define('INTEGER_REGEX', '/^[0-9]+$/');// 正規表現 半角数字

define('ITEM_TYPE', array(
    'illust' => 1,
    'music' => 2,
));

define('ITEM_STATUS', array(
    'close' => 0,
    'open' => 1,
));