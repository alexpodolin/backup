<?php
ini_set('error_reporting', 0);


session_start();
$_SESSION['srvAddr'] = $_POST['srvToEdit'];
$_SESSION['login'] = $_POST['authLogin'];
$_SESSION['passwd'] = $_POST['authPasswd'];
$_SESSION['portNumber'] = $_POST['portNumber'];
$pty = true;
$fPath = "/etc/rsyncd.conf";
$tmpDir = "/tmp/rsyncd.conf.tmp";

//Подкл. и авторизация
$connection = ssh2_connect($_SESSION['srvAddr'], $_SESSION['portNumber']);
ssh2_auth_password($connection, $_SESSION['login'], $_SESSION['passwd']);      
$errorStream = ssh2_fetch_stream($connection, SSH2_STREAM_STDERR);    
stream_set_blocking($errorStream, true);
stream_set_blocking($connection, true);    
//echo "Output: " . stream_get_contents($connection);
//echo "Error: " . stream_get_contents($errorStream);    
fclose($errorStream);
fclose($connection);

ssh2_scp_recv($connection, $fPath, $tmpDir); //Скачивание файла
chmod($tmpDir, 0777); //Изменим права доступа к файлу

//Выведем содержимое файла
echo $fileContent = file_get_contents($tmpDir);
$contentToEdit = $_POST['editFile'];
