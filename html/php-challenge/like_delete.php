<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    if ((isset($_GET['post_id'])) && (preg_match('/^\d+$/', $_GET['post_id']))) {
        //いいねを削除
        $like_add = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
        $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
            $_GET['post_id'], //いいねをする投稿されたツイートのid
            $_SESSION['id'] //いいねをしたメンバーのid
        ));
    } elseif (preg_match('/^\d+$/', $_GET['retweet_post_id'])) {
        $like_add = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
        $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
            $_GET['retweet_post_id'], //リツイートにいいねを消す場合のリツイート元のツイートのid
            $_SESSION['id'] //いいねをしたメンバーのid
        ));
    }
}
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
