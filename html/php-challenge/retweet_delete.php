<?php
session_start();
require('dbconnect.php');

//ログインしているユーザーのユーザーidを取得
$members = $db->prepare('SELECT * FROM members WHERE id=?');
$members->execute(array($_SESSION['id']));
$member = $members->fetch();

//投稿を調査(リツイートするメッセージ内容)
$retweetresponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetresponse->execute(array($_REQUEST['retweetRes']));
$table = $retweetresponse->fetch();
$retweet_message = $table['message'];
$retweet_messagepost = $table['retweet_post_id']; //リツイート元の投稿id（リツイートされた投稿をリツイートする場合値が入る）
echo 'リツイートid:';
print_r($retweet_messagepost); //リツイートされた投稿をリツイートする場合にリツイート元の値が入る

if (isset($_REQUEST['retweetRes'])) { //リツイート元でリツイート先を削除


    $like_add = $db->prepare('DELETE FROM posts WHERE member_id=? AND retweet_post_id=?');
    $like_add->execute(array(
        $member['id'], //ログインしているメンバーのid
        $_REQUEST['retweetRes'] //リツイートを削除するツイートのid
    ));
    echo '/リツイートしたメンバーのid:', $member['id'];
    echo '/リツイート元の投稿id:', $_REQUEST['retweetRes'];
    echo '/削除成功';
} else if (isset($_REQUEST['retweetmyRes'])) { //リツイートした投稿自身を削除


    $like_add = $db->prepare('DELETE FROM posts WHERE member_id=? AND posts.id=?');
    $like_add->execute(array(
        $member['id'], //ログインしているメンバーのid
        $_REQUEST['retweetmyRes'] //リツイートを削除するツイートのid 
    ));
    echo '/リツイートしたメンバーのid:', $member['id'];
    echo '/リツイートした投稿id:', $_REQUEST['retweetmyRes'];
    echo '/削除成功';
}
//header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
//exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
