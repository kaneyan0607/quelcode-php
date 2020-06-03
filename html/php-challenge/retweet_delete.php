<?php
session_start();
require('dbconnect.php');

//投稿を調査
$retweetResponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetResponse->execute(array($_GET['post_id']));
$table = $retweetResponse->fetch();
$retweetMessage = $table['message'];
$retweetMessagepost = $table['retweet_post_id']; //リツイート元の投稿id

if (isset($_SESSION['id'])) {
    if ((isset($_GET['post_id'])) && (preg_match('/^\d+$/', $_GET['post_id'] > 0))) {

        //リツイート元でリツイート先を削除
        $likeAdd = $db->prepare('DELETE FROM posts WHERE member_id=? AND retweet_post_id=?');
        $likeAdd->execute(array(
            $_SESSION['id'],
            $_GET['post_id']
        ));
    } elseif (preg_match('/^\d+$/', $_GET['retweet_orig'] > 0)) {

        //リツイートした投稿自身を削除
        $likeAdd = $db->prepare('DELETE FROM posts WHERE member_id=? AND posts.id=?');
        $likeAdd->execute(array(
            $_SESSION['id'],
            $_GET['retweet_orig']
        ));
    }
}
header('Location: index.php');
exit();
