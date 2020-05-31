<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id'])); //ログインしているユーザーの情報
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
			$member['id'], //DBからとってきたidの方が正確なので$SESSIONではない
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

// 返信の場合
if (isset($_REQUEST['res'])) { //Reをクリックしたら
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	$response->execute(array($_REQUEST['res']));

	$table = $response->fetch();
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

//※※追加機能いいね※※　自分がいいねした投稿の情報を取得する※※　likesテーブルからメッセージIDごとのいいねされた件数を取得する。メッセージIDごとにいいねされた件数を付加した状態に置き換える

$likeMessage_db = $db->prepare('SELECT likes.id, liked_post_id FROM likes WHERE pressed_member_id=?'); //SQLの雛形を作ってる。
// $likeMessage_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes GROUP BY liked_post_id');
$likeMessage_db->bindParam(1, $member['id'], PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）SQLの?の可変の部分に値を渡して置換してくれる。
$likeMessage_db->execute(); //実行
$likeMessages_db = $likeMessage_db->fetchAll();
//echo "いいね情報:";
//print_r($likeMessages_db);
//echo '/';
//※※追加機能ここまで※※

//※追加機能リツイート※　

//リツイート情報の取得 ※自分のリツイートしているリツイート情報のみ取得する。　for文、if文でon offを扱う。
$retweetMessage_db = $db->prepare('SELECT posts.id, retweet_post_id FROM posts WHERE member_id=? AND retweet_post_id'); //SQLの雛形を作ってる。
// $retweetMessage_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes GROUP BY liked_post_id');
$retweetMessage_db->bindParam(1, $member['id'], PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）SQLの?の可変の部分に値を渡して置換してくれる。
$retweetMessage_db->execute(); //実行
$retweetMessages_db = $retweetMessage_db->fetchALL();
//echo "現在ログインしているユーザのリツイート情報:";
//print_r($retweetMessages_db);

//リツイート情報の取得（GUI）
$posts2 = $db->query('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');
$posts2->execute();
$table2 = $posts2->fetchALL();
//echo 'リツイート情報の取得（GUI）/';
//print_r($table2);
////※※追加機能ここまで※※

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
					<!-- リツイート　-->
					<?php
					//メンバーテーブルから自分の名前を取得（リツイート時に使用）
					$myname_db = $db->prepare('SELECT retweet_post_id, members.name FROM members JOIN posts ON posts.member_id = members.id AND posts.id = ?');
					$myname_db->execute(array($post['id']));
					$myname = $myname_db->fetch();
					$mynameretweet = '<i class="fas fa-retweet"></i>' . $myname['name'] . 'さんがリツイート';
					//print_r($myname);
					//echo "/";
					//print_r($post['id']);
					?>
					<?php //echo (($post['id']) . '/' . ($post['retweet_post_id']));
					?>
					<!-- リツイート元の画像とメッセージと名前を表示 -->

					<?php
					if ($post['retweet_post_id'] > 0) : ?>
						<?php for ($i = 0; $i < count($table2); $i++) : ?>
							<?php if ($table2[$i]['id'] == $post['retweet_post_id']) : ?>
								<!-- もしも投稿idとリツイートしているidが一致したらリツイート元の本家のメッセージと名前を出力 & リツイートした人の名前を出力-->
								<img src="member_picture/<?php echo h($table2[$i]['picture']); ?>" width="48" height="48" alt="<?php echo h($table2[$i]['name']); ?>" />
								<p style="font-size:11px; color:#808000;"><?php echo $mynameretweet ?></p>
								<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($table2[$i]['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
							<?php endif; ?>
						<?php endfor; ?>
					<?php else : ?>
						<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
						<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
					<?php endif; ?>
					<!-- ここまで　-->


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
						<?php
						$retweetmyself = 0; //初期化
						$retweetself = 0;
						for ($i = 0; $i < count($retweetMessages_db); $i++) {
							if ($retweetMessages_db[$i]['retweet_post_id'] === $post['id']) { //リツイートされた元のid == 投稿idが一致場合　（リツイート元本家の処理）
								$retweetmyself = $post['id'];
								//echo 'リツイートfor文:';
								//print_r($retweetMessages_db[$i]['retweet_post_id']); // リツイート元のid
								//echo '/';
								//print_r($retweetMessages_db[$i]['id']); //リツイートした投稿id
								//echo '/';
								//print_r($retweetMessages_db[$i]);
							} else if ($retweetMessages_db[$i]['id'] === $post['id']) { //リツイートを投稿したidと投稿id一致で数値を入れる。
								$retweetself = $post['id'];
								//echo 'リツイートfor文の内側:';
								//print_r($retweetMessages_db[$i]['id']);
							} else if ($retweetMessages_db[$i]['retweet_post_id'] === $post['retweet_post_id']) { //リツイートをリツイートするときの挙動
								$retweetretweet = $post['retweet_post_id'];
								//echo 'リツイートをリツイートしたとき:';
								//print_r($post['retweet_post_id']);
							}
						}

						//echo '/リツイートした投稿id:';
						//print_r($retweetmyself);
						//echo '/';
						//print_r($retweetself);
						//↑このidを使ってリツイート先のデータを抽出する。

						?>
						<?php
						//echo '自分の投稿のリツイート元id';
						//print_r($post['retweet_post_id']);
						//echo '/リツート元idと投稿idが一致した場合:';
						//print_r($retweetmyself);
						//echo '/リツイートを投稿したidと投稿idが一致した場合:';
						//print_r($retweetself);
						//echo '/自分以外のユーザとリツイート元のidが一致した場合:';
						//print_r($retweetretweet);
						?>
						<!-- リツイート削除 -->
						<?php if ($retweetmyself === $post['id']) { //リツイート元（本家）でリツイート先を削除 
						?>　
						<a style="color:#0000FF;" href="retweet_delete.php?retweetRes=<?php echo h($post['id']); ?>"><i class="fas fa-retweet"></i></a>
					<?php } elseif ($retweetself === $post['id']) { //リツイートした投稿自体を削除
					?>
						<a style="color:#0000FF;" href="retweet_delete.php?retweetmyRes=<?php echo h($post['id']); ?>"><i class="fas fa-retweet"></i></a>
					<?php } elseif ($post['retweet_post_id'] === $retweetretweet) { //既に自分がリツイート済みで、誰かが同じ投稿をリツイートしている場合、その誰かのリツイートボタンを押した場合、自分がリツートした投稿を削除　 //リツイート元のidを飛ばして現在ログインしているユーザーのリツイート元idと一致した場合に削除させる
					?>
						<a style="color:#0000FF;" href="retweet_delete.php?retweetRes=<?php echo h($post['retweet_post_id']); ?>"><i class="fas fa-retweet"></i></a>
					<?php } else { ?>
						<!-- リツイートする　-->
						<a href="retweet_add.php?retweetRes=<?php echo h($post['id']); ?>"><i class="fas fa-retweet"></i></a>
					<? } ?>

					<?php //リツイート件数の表示
					$retweet_db = $db->prepare('SELECT COUNT(*) FROM posts WHERE retweet_post_id > 0 AND retweet_post_id = ? OR retweet_post_id = ?'); //0はカウントしない
					$retweet_db->bindParam(1, $post['retweet_post_id'], PDO::PARAM_INT);
					$retweet_db->bindParam(2, $post['id'], PDO::PARAM_INT);
					$retweet_db->execute();
					$retweets_db = $retweet_db->fetch();
					print_r($retweets_db['COUNT(*)']);

					?>

					<!-- リツイートここまで -->


					<!--　いいね機能 $postはいいねをする投稿されたツイートのid $likeMessageは53行目で取得したもの $likeMessages_dbには自分がいいねしたツイートの値のみ入っている。-->
					<?php
					$likemyself = 0; //初期化
					for ($i = 0; $i < count($likeMessages_db); $i++) {
						if ($likeMessages_db[$i]['liked_post_id'] == $post['id']) { //liked_post_id:いいねされたメッセージid == いいねをするツイートのidだった場合　これらが共通する場合に変数にその値を代入。$likeMessages_db[$i]['liked_post_id']は２次元配列と連想配列。
							$likemyself = $post['id'];
							//echo '本家のいいね判定:';
							//print_r($likemyself);
							//echo '//';
							//print_r($likeMessages_db[$i]);
						} else if ($likeMessages_db[$i]['liked_post_id'] == $post['retweet_post_id']) { //いいねの投稿idとリツートidが一致した場合。
							//echo 'リツイートへいいねした場合の判定:';
							$retweet_delete_like = $post['retweet_post_id'];
							//print_r($retweet_delete_like);
						}
					}

					//リツイートした投稿については、本家の投稿idを出力するfor文↓
					if ($post['retweet_post_id'] > 0) {
						for ($i = 0; $i < count($table2); $i++) {
							if ($table2[$i]['id'] == $post['retweet_post_id']) {
								$retweet_like = $post['retweet_post_id'];
								//echo 'リツイート（本家のid）/';
								//print_r($retweet_like);
							}
						}
					} else {
						echo '本家/';
						print_r($post['retweet_post_id']);
					}
					?>
					<!--　♥  $likemyselfには、自分がいいねした投稿の場合、いいねした投稿のidの数値が入っている。いいねしていなければidの値が無い為0になる。-->
					<?php if ($likemyself > 0) { ?>
						<a href="like_delete.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="font-size:18px; text-decoration:none; color:#FF0000;">&#9829;</a>
					<?php } elseif ($retweet_delete_like === $post['retweet_post_id']) { ?>
						<!-- リツイートにいいねした場合、$retweet_delete_likeに値が入っている。$post['retweet_post_id']と一致すればリツイートへのいいねidを削除するリンクへ誘導-->
						<a href="like_delete.php?id=<?php echo htmlspecialchars($post['retweet_post_id']); ?>" style="font-size:18px; text-decoration:none; color:#FF0000;">&#9829;</a>
						<!-- ♡ -->
					<?php } elseif ($post['retweet_post_id'] === $retweet_like) { ?>
						<!-- リツイートでいいねすると、本家に+1される。-->
						<a href="like_add.php?id=<?php echo htmlspecialchars($post['retweet_post_id']); ?>" style="font-size:12px; text-decoration:none; color:#FF0000;">&#9825;</a>
					<?php } else { ?>
						<a href="like_add.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="font-size:12px; text-decoration:none; color:#FF0000;">&#9825;</a>
						<!-- いいねここまで / $post['id']でliked_post_idを渡している。-->
					<?php } ?>

					<?php
					if ($post['retweet_post_id'] > 0) {
						for ($i = 0; $i < count($table2); $i++) {
							if ($table2[$i]['id'] == $post['retweet_post_id']) {
								//リツイートが、リツイート元のいいね数を出力する。
								$likerecord_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes WHERE liked_post_id = ?');
								$likerecord_db->bindParam(1, $retweet_like, PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）bindparamはSQLの「?」の可変の部分に値を渡して置換してくれる。
								$likerecord_db->execute(); //実行 $post['id']は投稿されているツイートのid
								$likerecords_db = $likerecord_db->fetch();
								print_r($likerecords_db['COUNT(*)']);
							}
						}
					} else {
						//※※追加機能いいね※※ いいねした件数を各投稿idごとに取得して表示する。
						$likerecord_db = $db->prepare('SELECT liked_post_id, COUNT(*) FROM likes WHERE liked_post_id = ?');
						$likerecord_db->bindParam(1, $post['id'], PDO::PARAM_INT); ////bindparamで順次させている。（１番目はこれ、２番目はこれみたいな、、、）bindparamはSQLの「?」の可変の部分に値を渡して置換してくれる。
						$likerecord_db->execute(); //実行 $post['id']は投稿されているツイートのid
						$likerecords_db = $likerecord_db->fetch();
						print_r($likerecords_db['COUNT(*)']);
					}

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