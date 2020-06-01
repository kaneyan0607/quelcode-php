<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    if (isset($_GET['post_id'])) {
        //いいねを投稿
        $like_add = $db->prepare('INSERT INTO likes SET liked_post_id=?, pressed_member_id=?, created=NOW()');
        $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
            $_GET['post_id'], //いいねをする投稿されたツイートのid
            $_SESSION['id'] //いいねをしたメンバーのid
        ));
    } else if (isset($_GET['retweet_post_id'])) {
        $like_add = $db->prepare('INSERT INTO likes SET liked_post_id=?, pressed_member_id=?, created=NOW()');
        $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
            $_GET['retweet_post_id'], //リツイートにいいねをした場合のリツイート元のツイートのid
            $_SESSION['id'] //いいねをしたメンバーのid
        ));
    }
}
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
