<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];
	
	// 投稿を検査する
	$messages = $db->prepare('SELECT * FROM posts WHERE id=?');
	$messages->execute(array($id));
	$message = $messages->fetch();

	if ($message['member_id'] === $_SESSION['id']) {
		// 削除する
		$del = $db->prepare('DELETE FROM posts WHERE id=?');
		$del->execute(array($id));
		//RTの削除
		$del_retweet = $db->prepare('DELETE FROM posts WHERE original_post_id=?'); 
		$del_retweet->execute(array($id));
		//いいねを削除
		$del_like = $db->prepare('DELETE FROM likes WHERE post_id=?');
		$del_like->execute(array($id));
	}
}

header('Location: index.php'); exit();
?>
