<?php
require_once(__DIR__.'/config.php');//__DIR__:該当フォルダ

//データベース（ユーザ）に接続
function connectDB(){
    try{
        return new PDO(DSN,DB_USER,DB_PASSWORD);
    }catch(PDOException $e){
        echo $e->getMessage();
        exit;
    }
}
?>
