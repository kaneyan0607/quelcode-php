<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    //いいねを削除
    $like_add = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
    $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
        $_GET['id'], //いいねをする投稿されたツイートのid
        $_SESSION['id'] //いいねをしたメンバーのid
    ));
} elseif (isset($_SESSION['retweet_post_id'])) {
    $like_add = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
    $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
        $_GET['retweet_post_id'], //いいねをする投稿されたツイートのid
        $_SESSION['id'] //いいねをしたメンバーのid
    ));
}

//echo 'いいねをする投稿のid', $_GET['id'];
//echo '/';
//echo 'いいねをしたメンバーのid', $_SESSION['id'];
//echo '/';
//echo '削除成功';
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
