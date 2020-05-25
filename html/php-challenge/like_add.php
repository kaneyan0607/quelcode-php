<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    //いいねを投稿
    $like_add = $db->prepare('INSERT INTO likes SET liked_post_id=?, pressed_member_id=?, created=NOW()');
    $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
        $_GET['id'], //いいねをする投稿されたツイートのid
        $_SESSION['id'] //いいねをしたメンバーのid
    ));
}

//echo 'いいねをする投稿のid', $_GET['id'];
//echo '/';
//echo 'いいねをしたメンバーのid', $_SESSION['id'];
//echo '/';
//echo '成功';
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
