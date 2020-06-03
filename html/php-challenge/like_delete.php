<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    if ((isset($_GET['post_id'])) && (preg_match('/^\d+$/', $_GET['post_id'] > 0))) {
        //いいねを削除
        $likeAdd = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
        $likeAdd->execute(array(
            $_GET['post_id'], //いいねをする投稿されたツイートのid
            $_SESSION['id']
        ));
    } elseif (preg_match('/^\d+$/', $_GET['retweet_post_id'] > 0)) {
        $likeAdd = $db->prepare('DELETE FROM likes WHERE liked_post_id=? AND pressed_member_id=?');
        $likeAdd->execute(array(
            $_GET['retweet_post_id'], //リツイートにいいねを消す場合のリツイート元のツイートのid
            $_SESSION['id']
        ));
    }
}
header('Location: index.php');
exit();
