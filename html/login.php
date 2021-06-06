<?php

require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'function.php';

// セッション開始
session_start();
// セッションにIDがセットされていれば商品一覧ページへ飛ぶ
if(login_check() === true){
    redirect_to(HOME_URL);
}

include_once VIEW_PATH . 'login_view.php';
