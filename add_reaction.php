<?php

// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// Ajaxリクエストからデータを取得
$commentId = $_POST['commentId'];
$reactionType = $_POST['reactionType'];

// リアクションを追加する関数の呼び出し
addReaction($commentId, $reactionType);

// レスポンスの作成
$response = array('success' => true);
echo json_encode($response);

// リアクションを追加する関数
function addReaction($commentId, $reactionType) {
    $reactionSql = 'INSERT INTO `reaction_tb` (`comment_id`, `reaction_type`) VALUES (:comment_id, :reaction_type)';
    
    // 実際のデータベースの接続とプリペアドステートメントの実行
    $dbh = connectDB(); // 適切なデータベース接続関数を呼び出す
    $reactionSth = $dbh->prepare($reactionSql);
    
    // パラメータのバインド
    $reactionSth->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
    $reactionSth->bindParam(':reaction_type', $reactionType, PDO::PARAM_STR);
    
    // データベースへの挿入実行
    $reactionSth->execute();
}
?>
