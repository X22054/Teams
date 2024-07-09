<?php
//セッションのスタート
session_start();

//ログイン確認
if (!(isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    //ログイン失敗：ログインフォーム画面
    header('Location: login.html');
    exit;
}

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');
$dbh = connectDB();//DBへの接続
if($dbh){
    //データベースへの問い合わせSQL文(文字列)
    $sql = 'SELECT `id`, `username`,`email` FROM `user_tb`';
$res = $dbh->query($sql);//SQLの実行
//取得したデータを配列に格納
$data = $res->fetchAll(PDO::FETCH_ASSOC);

//ヘッダの指定でjsonの動きを安定させる
header('Content-type: application/json');
$json = json_encode($data);//JSON形式に変換
echo $json;//JSON形式のデータを出力
$dbh = null;//DBを閉じる
}