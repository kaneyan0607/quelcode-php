<?php
session_start();
require('dbconnect.php');

//投稿を調査(リツイートするメッセージ内容)
$retweetresponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetresponse->execute(array($_GET['post_id']));
$table = $retweetresponse->fetch();
$retweet_message = $table['message']; //リツイート元の投稿されたメッセージ
$retweet_messagepost = $table['retweet_post_id']; //リツイート元の投稿id（リツイートされた投稿をリツイートする場合値が入る）

if (isset($_SESSION['id'])) {
    if (isset($_GET['post_id']) && ($retweet_messagepost == 0)) { //リツイート元からのリツイートをするためリツイート元のidを入れる

        //リツイートを投稿
        $retweet_db = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
        $retweet_db->execute(array(
            $_SESSION['id'],
            $retweet_message,
            $_GET['post_id']
        ));
    } else { //リツイートされた投稿をリツイートする際、リツート元の投稿idをretweet_post_idカラムに入れる。

        //リツイートを投稿
        $retweet_db = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
        $retweet_db->execute(array(
            $_SESSION['id'],
            $retweet_message,
            $retweet_messagepost
        ));
    }
}
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
