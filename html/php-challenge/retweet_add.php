<?php
session_start();
require('dbconnect.php');

//投稿を調査
$retweetResponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetResponse->execute(array($_GET['post_id']));
$table = $retweetResponse->fetch();
$retweetMessage = $table['message']; //リツイート元の投稿されたメッセージ
$retweetMessagepost = $table['retweet_post_id']; //リツイート元の投稿id

if ((isset($_SESSION['id'])) && (preg_match('/^\d+$/', $_GET['post_id'] > 0))) {
    if (isset($_GET['post_id']) && ($retweetMessagepost == 0)) {

        //リツイートを投稿
        $retweetDb = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
        $retweetDb->execute(array(
            $_SESSION['id'],
            $retweetMessage,
            $_GET['post_id']
        ));
    } else {

        //リツイートされた投稿をリツイート
        $retweetDb = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
        $retweetDb->execute(array(
            $_SESSION['id'],
            $retweetMessage,
            $retweetMessagepost
        ));
    }
}
header('Location: index.php');
exit();
