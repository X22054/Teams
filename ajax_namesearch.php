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

$search_name = htmlspecialchars($_POST['search_name'], ENT_QUOTES, 'UTF-8');

//DBへの接続
$dbh = connectDB();
if ($dbh) {
    //データベースへの問い合わせSQL文(文字列)
    $sql = 'SELECT * FROM `user_tb` WHERE `username` LIKE :search_name OR `email` LIKE :search_name';
    $sth = $dbh->prepare($sql);
    //検索文字列がどこかに含まれている場合に一致するように
    $sth->bindValue(':search_name', '%' . $search_name . '%', PDO::PARAM_STR);
    $sth->execute();

    //取得したデータを配列に格納
    $data = $sth->fetchAll(PDO::FETCH_ASSOC);

    //ヘッダの指定でjsonの動きを安定させる
    header('Content-type: application/json');
    $json = json_encode($data); //JSON形式に変換
    echo $json; //JSON形式のデータを出力
    $dbh = null; //DBを閉じる
}
?>
