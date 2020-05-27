<?php
session_start();
require('dbconnect.php');

//投稿を調査
$retweetresponse = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$retweetresponse->execute(array($_REQUEST['res']));
$table = $retweetresponse->fetch();
$retweet_message = $table['message'];
$retweet_message_id = $table['member_id'];
print_r($table);
echo '/';
print_r($retweet_message);
echo '/';
print_r($retweet_message_id);
echo '/';

if (isset($_SESSION['id'])) {
    //リツイートを投稿
    $like_add = $db->prepare('INSERT INTO posts SET message=?,  member_id=?, retweet_member_id=?, retweet_post_id=?, created=NOW()');
    $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
        $retweet_message,
        $retweet_message_id,
        $_SESSION['id'], //リツイートをしたメンバーのid
        $_REQUEST['res'] //リツイートをするツイートのid
    ));
}

echo '/';
echo 'リツイートをする投稿のid', $_REQUEST['res'];
echo '/';
echo 'リツイートメンバーのid', $_SESSION['id'];
echo '/';
echo '成功';
//header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
//exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
