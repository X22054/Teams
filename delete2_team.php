<?php
session_start();

if (!(isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    header('Location: login.html');
    exit();
}

require_once(__DIR__ . '/functions.php');

if (isset($_SESSION['delete'])) {
    $teamdToDelete = $_SESSION['delete'];

    $deleteFilesQuery = "DELETE FROM `file_tb` WHERE `team_id` = '$teamIdToDelete'";

    // DB connection
    $dbh = connectDB();

    if ($dbh) {
        $dbh->query($deleteFilesQuery); 

        $deleteTeamQuery = "DELETE FROM `team_tb` WHERE `id` = '$teamIdToDelete'";
        $sth = $dbh->query($deleteTeamQuery);

        // Initialization
        unset($_SESSION['delete']);
    }
    header('Location: show_team.php');
}
?>
