<?php
session_start();
require('dbconnect.php');

//投稿を調査(リツイートするメッセージ内容)
$retweetresponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetresponse->execute(array($_GET['post_id']));
$table = $retweetresponse->fetch();
$retweet_message = $table['message'];
$retweet_messagepost = $table['retweet_post_id']; //リツイート元の投稿id（リツイートされた投稿をリツイートする場合値が入る）

if (isset($_SESSION['id'])) {
    if (isset($_GET['post_id'])) { //リツイート元でリツイート先を削除


        $like_add = $db->prepare('DELETE FROM posts WHERE member_id=? AND retweet_post_id=?');
        $like_add->execute(array(
            $_SESSION['id'], //ログインしているメンバーのid
            $_GET['post_id'] //リツイートを削除するツイートのid
        ));
    } else if (isset($_GET['retweet_orig'])) { //リツイートした投稿自身を削除


        $like_add = $db->prepare('DELETE FROM posts WHERE member_id=? AND posts.id=?');
        $like_add->execute(array(
            $_SESSION['id'], //ログインしているメンバーのid
            $_GET['retweet_orig'] //リツイートを削除するツイートのid 
        ));
    }
}
header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
