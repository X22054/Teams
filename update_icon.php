<?php
// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// DBへの接続
$dbh = connectDB();

// チームIDの取得
if (isset($_GET['team_id'])) {
    $team_id = $_GET['team_id'];
}

// ユーザーIDの取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// アップロードされたアイコンの処理
if ($_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['icon']['tmp_name'];
    $file_name = basename($_FILES['icon']['name']);
    $upload_dir = 'uploads/';  // アップロード先のディレクトリ

    // ファイルの移動
    if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
        // データベースのファイル情報を更新する処理を追加
        $file_path = $upload_dir . $file_name;

        // 既存のファイル情報を取得
        $select_sql = 'SELECT * FROM file_tb WHERE team_id = :team_id';
        $select_sth = $dbh->prepare($select_sql);
        $select_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
        $select_sth->execute();
        $existing_file = $select_sth->fetch();

        if ($existing_file) {
            // 既存のファイル情報が存在する場合は更新
            $update_sql = 'UPDATE file_tb SET file_name = :file_name, file_path = :file_path, uploaded_at = :uploaded_at WHERE team_id = :team_id';
            $update_sth = $dbh->prepare($update_sql);
            $uploaded_at = date('Y-m-d H:i:s');
            $update_sth->bindParam(':file_name', $file_name, PDO::PARAM_STR);
            $update_sth->bindParam(':file_path', $file_path, PDO::PARAM_STR);
            $update_sth->bindParam(':uploaded_at', $uploaded_at, PDO::PARAM_STR);
            $update_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);

            if ($update_sth->execute()) {
                echo 'アイコンを変更しました。';
            } else {
                echo 'ファイル情報の更新に失敗しました。';
            }
        } else {
            // 既存のファイル情報が存在しない場合は新規挿入
            $insert_sql = 'INSERT INTO file_tb (team_id, file_name, file_path, uploaded_at) VALUES (:team_id, :file_name, :file_path, :uploaded_at)';
            $insert_sth = $dbh->prepare($insert_sql);
            $insert_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            $insert_sth->bindParam(':file_name', $file_name, PDO::PARAM_STR);
            $insert_sth->bindParam(':file_path', $file_path, PDO::PARAM_STR);
            $insert_sth->bindParam(':uploaded_at', $uploaded_at, PDO::PARAM_STR);

            if ($insert_sth->execute()) {
                echo 'アイコンを変更しました。';
            } else {
                echo 'ファイル情報の保存に失敗しました。';
            }
        }
    } else {
        echo 'ファイルのアップロードに失敗しました。';
    }
} else {
    echo 'アイコンのアップロードに失敗しました。';
}
echo '<a href="team_page.php?team_id=' . $team_id . '&public=' . $_SESSION['public'] . '" name="team_id">チームトップに戻る</a>';
?>
