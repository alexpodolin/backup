<?php
//ini_set('error_reporting', 0);

session_start();

//$srvAddr = $_POST['srvToEdit'];
//$login = $_POST['authLogin'];
//$passwd = $_POST['authPasswd'];
//$portNumber = $_POST['portNumber'];
$pty = true;
$fPath = "/etc/rsyncd.conf";
$tmpDir = "/tmp/rsyncd.conf.tmp";
$tmpRemoteDir = "/tmp/rsyncd.conf.tmp";
$cmdCopyConf = "echo " . $_SESSION['passwd'] . " | sudo -S mv /tmp/rsyncd.conf /etc/ && chmod 644 /etc/rsyncd.conf";
$cmdChownConf = "echo " . $_SESSION['passwd'] . " | sudo -S chown root:root /etc/rsyncd.conf";

//Примем через ajax новый отредактированный конфиг и сохраним его в файл
$fileOpen = fopen($tmpDir, 'w');
fwrite($fileOpen, $_POST['editFile']);    
fclose($fileOpen);

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

//Положим отредактированный файл обратно на сервер во временную папку
//ssh2_scp_send($connection, $tmpDir, '/etc/rsyncd.conf', 0644);
ssh2_scp_send($connection, $tmpDir, '/tmp/rsyncd.conf', 0644);

//Скопируем из временной папки в /etc
$copyConfFile=ssh2_exec($connection, $cmdCopyConf, $pty); 	
$errorStream = ssh2_fetch_stream($copyConfFile, SSH2_STREAM_STDERR);
stream_set_blocking($errorStream, true);
stream_set_blocking($copyConfFile, true);    
stream_get_contents($copyConfFile);
stream_get_contents($errorStream);    
fclose($errorStream);
fclose($copyConfFile);

//Поменяем владельца конфига
$chownConfFile=ssh2_exec($connection, $cmdChownConf, $pty); 	
$errorStream = ssh2_fetch_stream($chownConfFile, SSH2_STREAM_STDERR);
stream_set_blocking($errorStream, true);
stream_set_blocking($chownConfFile, true);    
stream_get_contents($chownConfFile);
stream_get_contents($errorStream);    
fclose($errorStream);
fclose($chownConfFile);

//Перезапустим процесс на сервера ч.б. конифг применился
$cmdReStart = "echo " . $_SESSION['passwd'] . " | sudo -S systemctl start rsyncd";
$reStartRsyncd = ssh2_exec($connection, $cmdReStart, $pty);
$errorStream = ssh2_fetch_stream($reStartRsyncd, SSH2_STREAM_STDERR);
stream_set_blocking($errorStream, true);
stream_set_blocking($reStartRsyncd, true);    
stream_get_contents($reStartRsyncd);
stream_get_contents($errorStream);    
fclose($errorStream);
fclose($reStartRsyncd); 

//Удалим файл с web сервера
unlink($tmpDir);

//Завершим подключение к серверу
ssh2_exec($connection, 'exit', $pty);
unset($srvAddr, $portNumber, $login, $passwd, $pty, $connection, $fPath, $tmpDir, $errorStream, $contentToEdit, $fileOpen  );
