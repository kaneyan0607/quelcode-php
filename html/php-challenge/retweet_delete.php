<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    //リツイートを投稿
    $like_add = $db->prepare('DELETE FROM posts WHERE retweet_member_id=? AND id=?');
    $like_add->execute(array( //PHP ExecuteコマンドはPHPスクリプトや関数を実行するために使用
        $_SESSION['id'], //リツイートをしたメンバーのid
        $_REQUEST['res'] //リツイートを削除するツイートのid
    ));
}

echo '/';
echo 'リツイートしたメンバーのid', $_SESSION['id'];
echo '/';
echo 'リツイートをする削除する投稿のid', $_REQUEST['res'];
echo '/';
echo '成功';
//header('Location: index.php'); //postの処理が行われた後、index.phpに戻る。
//exit();
//$_SESSIONは、PHPの定義済み変数のセッション変数です。この変数は、配列型の配列変数であり、現在のセッションに登録されている値から渡されたデータが格納されている変数です。
