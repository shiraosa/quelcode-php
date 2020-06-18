<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id = ?');
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

//RT機能

//いいね機能
?>


<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>
	<link rel="stylesheet" href="style.css" />
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
    rel="stylesheet">

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
				//RTの場合
				if(!is_null($post['original_post_id'])){
					$origin_post = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
					$origin_post->execute(array($post['original_post_id']));
					$origin = $origin_post->fetch();
					$retweet_from = $post['name'];
					$post['name'] = $origin['name'];
					$post['picture'] = $origin['picture'];
					$post['member_id'] = $origin['member_id'];
					$post['id'] = $post['original_post_id'];
				}
			?>
				<div class="msg">
					<?php
					if(!is_null($post['original_post_id'])){
					?>
						<p style="font-size:12px;margin-left:48px;"><?php echo $retweet_from."さんがリツイートしました";?></p>
					<?PHP
					}
					?>
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
							[<a href = "delete.php?id=<?php echo h($post['id']); ?>" style = "color: #F33;">削除</a>]
						<?php
						endif;
						?>
						<?php
						//RT機能
						$rtweets = $db->prepare('SELECT member_id FROM posts WHERE original_post_id=?');
						$rtweets->execute(array($post['id']));
						unset($rt_check);
						foreach($rtweets as $rt){
							$rt_check[] = $rt['member_id'];  
						}
						if(in_array($_SESSION['id'],(array)$rt_check)){
						?>
							<a href="retweet.php?id=<?php echo h($post['id']); ?>" alt="リツイート"><i class = "material-icons" style = "vertical-align: middle;color:limegreen">repeat</i></a>
						<?php
						}else{
						?>
							<a href="retweet.php?id=<?php echo h($post['id']); ?>" alt="リツイート"><i class = "material-icons" style = "vertical-align: middle;">repeat</i></a>
						<?PHP
						}
						?>
						<?php
						//RT件数表示
						$rtweets_count = $db->prepare('SELECT COUNT(*) FROM posts WHERE original_post_id=?');
						$rtweets_count->execute(array($post['id']));
						$rtweet_count = $rtweets_count->fetchColumn();
						if($rtweet_count > 0){
							echo $rtweet_count;
						}
							?>
						<?php
						//いいね機能
						$likes = $db->prepare('SELECT * FROM likes WHERE post_id = ?');
						$likes->execute(array($post['id']));
						unset($like_check);
						foreach($likes as $like){
							$like_check[] = $like['member_id'];
						}
						if(in_array($_SESSION['id'],(array)$like_check)){
						?>
							<a href="like.php?id=<?php echo h($post['id']); ?>" alt="いいね"><i class="material-icons" style="vertical-align: middle;color:red">favorite</i></a>
						<?php
						}else{
						?>
							<a href="like.php?id=<?php echo h($post['id']); ?>" alt="いいね"><i class="material-icons" style="vertical-align: middle;">favorite</i></a>
						<?php
						}
						//いいね件数表示
						$likes_count = $db->prepare('SELECT  COUNT(*) AS likes_count FROM likes WHERE post_id = ?');
						$likes_count->execute(array($post['id']));
						$like_count = $likes_count->fetchColumn();
						if($like_count > 0){
							echo $like_count;
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