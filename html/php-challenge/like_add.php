<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    if ((isset($_GET['post_id'])) && (preg_match('/^\d+$/', $_GET['post_id'] > 0))) {
        //いいねを投稿
        $likeAdd = $db->prepare('INSERT INTO likes SET liked_post_id=?, pressed_member_id=?, created=NOW()');
        $likeAdd->execute(array(
            $_GET['post_id'], //いいねをする投稿されたツイートのid
            $_SESSION['id']
        ));
    } elseif (preg_match('/^\d+$/', $_GET['retweet_post_id'] > 0)) {
        $likeAdd = $db->prepare('INSERT INTO likes SET liked_post_id=?, pressed_member_id=?, created=NOW()');
        $likeAdd->execute(array(
            $_GET['retweet_post_id'], //リツイートにいいねをした場合のリツイート元のツイートのid
            $_SESSION['id']
        ));
    }
}
header('Location: index.php');
exit();
