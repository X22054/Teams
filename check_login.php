<?php
//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

//セッションの生成
session_start();
if (!(isset($_POST['email']) && $_POST['email'] != ""
    && isset($_POST['password']) && $_POST['password'] != "")) {
    header('Location:login.html');
}

//ユーザ名/パスワード
$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
$password = htmlspecialchars($_POST['password'], ENT_QUOTES);
//DBへの接続
$dbh = connectDB();

if ($dbh) {
    //データベースへの問い合わせSQL文(文字列)
    $sql = "SELECT*FROM`user_tb`WHERE`email` =  :email";
    $sth = $dbh->prepare($sql); //SQLの準備
    $sth->bindValue(':email', $email, PDO::PARAM_STR); //値のバインド
    $sth->execute(); //SQLの実行
    $result = $sth->fetchALL(PDO::FETCH_ASSOC); //データの取得
    if (count($result) == 1) { //配列数が唯一の場合
        if (password_verify($password, $result[0]['password'])) {
            $login = 'OK'; //ログイン成功
            $_SESSION['username'] = $result[0]['username']; //ユーザ名をセッション変数に保存
            $_SESSION['id'] = $result[0]['id']; //ユーザIDをセッション変数に保存
            // $_SESSION['is_admin'] = $result[0]['is_admin']; //
        } else {
            //ログイン失敗
            $login = 'Error';
        }
    } else {
        //ログイン失敗
        $login = 'Error';
    }
}

$sth = null; //データの消去
$dbh = null; //DBを閉じる

//セッション変数に代入
$_SESSION['login'] = $login;

//移動
if ($login == 'OK') {
    //ログイン成功:掲示板メニュー
    header('Location:top_page.php');
    exit(); //exit();もしくはdie();
} else {
    //ログイン失敗：ログインフォーム画面へ
    header('Location:login.html');
    exit();
}
