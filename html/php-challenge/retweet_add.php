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
print_r($retweet_messagepost);

if (isset($_REQUEST['retweetRes']) && ($retweet_messagepost == 0)) { //リツイート元からのリツイートをするためリツイート元のidを入れる

    //リツイートを投稿
    $retweet_db = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
    $retweet_db->execute(array(
        $member['id'], //DBからとってきたidの方が正確なので$SESSIONではない
        $retweet_message,
        $_REQUEST['retweetRes']
    ));
    echo '/リツイートするメンバーのid:';
    print_r($member['id']);
    echo '/リツイート元のメッセージ:';
    print_r($retweet_message);
    echo '/リツイート元の投稿id:';
    print_r($_REQUEST['retweetRes']);
    echo '/INSERT成功';
    //header('Location: index.php');
    //exit();
} else if (isset($_REQUEST['retweetRes']) && ($retweet_messagepost > 0)) { //リツイートされた投稿をリツイートする際、リツート元の投稿idをretweet_post_idカラムに入れる。

    //リツイートを投稿
    $retweet_db = $db->prepare('INSERT INTO posts SET member_id=?, message=?, retweet_post_id=?, created=NOW()');
    $retweet_db->execute(array(
        $member['id'], //DBからとってきたidの方が正確なので$SESSIONではない
        $retweet_message,
        $retweet_messagepost
    ));
    echo '/※リツイートのリツイートを投稿。リツイートするメンバーのid:';
    print_r($member['id']);
    echo '/リツイート元のメッセージ:';
    print_r($retweet_message);
    echo '/リツイート元の投稿id:';
    print_r($retweet_messagepost);
    echo '/INSERT成功';
}
