<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id']));
	$member = $members->fetch();
} else {
	// ログインしていない
	header('Location: login.php');
	exit();
}

// 投稿を記録する
if (!empty($_POST)) {
	if ($_POST['message'] != '') {
		$message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
		$message->execute(array(
			$member['id'],
			$_POST['message'],
			$_POST['reply_post_id']
		));

		header('Location: index.php');
		exit();
	}
}

// 投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
	$page = 1;
}
$page = max($page, 1);

// 最終ページを取得する
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$start = max(0, $start);

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

//※※追加機能　自分がいいねした投稿の情報を取得する※※　likesテーブルからメッセージIDごとのいいねされた件数を取得する。メッセージIDごとにいいねされた件数を付加した状態に置き換える

$likeMessage_db = $db->prepare('SELECT liked_post_id FROM likes WHERE pressed_member_id=?');

// $likeMessage_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes GROUP BY liked_post_id'); //SQLの雛形を作ってる。
$likeMessage_db->bindParam(1, $_SESSION['id'], PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）SQLの?の可変の部分に値を渡して置換してくれる。
$likeMessage_db->execute(); //実行
$likeMessages_db = $likeMessage_db->fetchAll();
print_r($likeMessages_db);
//※※追加機能ここまで※※

//※追加機能※　「likes」テーブルより自身がいいねしたメッセージIDの一覧情報を作り出す
//いいねした件数を各投稿idごとに取得する。

////※※追加機能ここまで※※

// 返信の場合
if (isset($_REQUEST['res'])) {
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	$response->execute(array($_REQUEST['res']));

	$table = $response->fetch();
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value)
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定します
function makeLink($value)
{
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>', $value);
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>
	<link rel="stylesheet" href="style.css" />
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>ひとこと掲示板</h1>
		</div>
		<div id="content">
			<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
			<form action="" method="post">
				<dl>
					<dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
					<dd>
						<textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
						<input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>" />
					</dd>
				</dl>
				<div>
					<p>
						<input type="submit" value="投稿する" />
					</p>
				</div>
			</form>

			<?php
			foreach ($posts as $post) :
			?>
				<div class="msg">
					<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
					<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>

					<p class="day"><a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
						<?php
						if ($post['reply_post_id'] > 0) :
						?>
							<a href="view.php?id=<?php echo
														h($post['reply_post_id']); ?>">
								返信元のメッセージ</a>
						<?php
						endif;
						?>
						<?php
						if ($_SESSION['id'] == $post['member_id']) :
						?>
							[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
						<?php
						endif;
						?>
						<!-- リツイート　-->
						<a href="index.php?res=<?php echo h($post['id']); ?>"><i class="fas fa-retweet"></i></a>
						<!-- リツイートここまで -->
						<!--　いいね機能 $postはいいねをする投稿されたツイートのid $likeMessageは53行目で取得したもの-->
						<?php
						$likemyself = 0; //初期化
						for ($i = 0; $i < count($likeMessages_db); $i++) {
							if ($likeMessages_db[$i]['liked_post_id'] == $post['id']) { //liked_post_id:いいねされたメッセージid == いいねをするツイートのidだった場合　これらが共通する場合に変数にその値を代入。$likeMessages_db[$i]['liked_post_id']は２次元配列と連想配列。
								$likemyself = $post['id'];
								//print_r($likeMessages_db[$i]['liked_post_id']);
								//echo '/';
								//print_r($likeMessages_db[$i]);
							}
						}
						//print_r($likemyself);

						?>
						<!--　♥  $likemyselfには、自分がいいねした投稿の場合、いいねした投稿のidの数値が入っている。いいねしていなければidの値が無い為0になる。-->
						<?php if ($likemyself > 0) { ?>
							<a href="like_delete.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="font-size:18px; text-decoration:none; color:#FF0000;">&#9829;</a>
							<!-- ♡ -->
						<?php } else { ?>
							<a href="like_add.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="font-size:12px; text-decoration:none; color:#FF0000;">&#9825;</a>
							<!-- いいねここまで / $post['id']でliked_post_idを渡している。-->
						<?php } ?>
						<?php
						//いいねした件数を各投稿idごとに取得する。
						$likerecord_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes WHERE liked_post_id = ?');

						//$likeMessage_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes GROUP BY liked_post_id'); //SQLの雛形を作ってる。
						//$likerecord_db->bindParam(1, $_SESSION['id'], PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）SQLの?の可変の部分に値を渡して置換してくれる。
						$likerecord_db->execute(array($post['id'])); //実行  $post['id']は投稿されているツイートのid
						$likerecords_db = $likerecord_db->fetch();
						print_r($likerecords_db['COUNT(*)']);
						?>
					</p>
				</div>
			<?php
			endforeach;
			?>

			<ul class="paging">
				<?php
				if ($page > 1) {
				?>
					<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
				<?php
				} else {
				?>
					<li>前のページへ</li>
				<?php
				}
				?>
				<?php
				if ($page < $maxPage) {
				?>
					<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
				<?php
				} else {
				?>
					<li>次のページへ</li>
				<?php
				}
				?>
			</ul>
		</div>
	</div>
</body>

</html>