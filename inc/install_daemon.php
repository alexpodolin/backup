<?php
//Установка rsyncd на сервер требуется наличие установленных библиотек OpenSSL и libssh2, версия libssh2 1.2.3 или новее

$serverName = $_POST['Server_name'];
$portNumber = $_POST['portnumber'];
$login = $_POST['loginname'];
$passwd = $_POST['password'];
$cmdInstall = "echo " . $passwd . " | sudo -S yum install rsync -y";
$cmdEnable = "echo " . $passwd . " | sudo -S systemctl enable rsyncd";
$cmdStart = "echo " . $passwd . " | sudo -S systemctl start rsyncd";
$cmdCopy = "echo " . $passwd . " | sudo -S mv /tmp/rsyncd.scrt /etc/ && chmod 600 /etc/rsyncd.scrt";
$cmdChown = "echo " . $passwd . " | sudo -S chown root:root /etc/rsyncd.scrt";
$cmdOpenPort = "echo " . $passwd . " | sudo -S firewall-cmd --permanent --add-port=873/tcp";
$cmdFirewallReload = "echo " . $passwd . " | sudo -S firewall-cmd --reload";
$pty = true;

if (isset($serverName)) {        
    //  Подкл. и авторизация
    $connection = ssh2_connect($serverName, $portNumber);
    ssh2_auth_password($connection, $login, $passwd);
    
    //  Уст. пакета
    $installPacket=ssh2_exec($connection, $cmdInstall, $pty); 
    $errorStream = ssh2_fetch_stream($installPacket, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($installPacket, true);    
    stream_get_contents($installPacket);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($installPacket);
    
    // Поставим в автозагрузку rsyncd
    $enableRsyncd=ssh2_exec($connection, $cmdEnable, $pty);    
    $errorStream = ssh2_fetch_stream($enableRsyncd, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($enableRsyncd, true);    
    stream_get_contents($enableRsyncd);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($enableRsyncd);
    
	//Положим файл авторизации для rsync, изменим права, 
	ssh2_scp_send($connection, 'files/rsyncd.scrt', '/tmp/rsyncd.scrt', 0664);	
	$copyScrtFile=ssh2_exec($connection, $cmdCopy, $pty); 	
    $errorStream = ssh2_fetch_stream($copyScrtFile, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($copyScrtFile, true);    
    stream_get_contents($copyScrtFile);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($copyScrtFile);
	
	//Выставим владельца
	$chownScrtFile=ssh2_exec($connection, $cmdChown, $pty);    
    $errorStream = ssh2_fetch_stream($chownScrtFile, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($chownScrtFile, true);    
    stream_get_contents($chownScrtFile);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($chownScrtFile);
	
	//Откроем порт 873 firewald
	$openFirewall=ssh2_exec($connection, $cmdOpenPort, $pty);
	$errorStream = ssh2_fetch_stream($openFirewall, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($openFirewall, true);    
    stream_get_contents($openFirewall);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($openFirewall);
	
	//Перезапустим firewald
	$reloadFirewall=ssh2_exec($connection, $cmdFirewallReload, $pty);
	$errorStream = ssh2_fetch_stream($reloadFirewall, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($reloadFirewall, true);    
    stream_get_contents($reloadFirewall);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($reloadFirewall);	
	    	
    // Стартанем демон rsyncd
    $startRsyncd=ssh2_exec($connection, $cmdStart, $pty);
    $errorStream = ssh2_fetch_stream($startRsyncd, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($startRsyncd, true);    
    stream_get_contents($startRsyncd);
    stream_get_contents($errorStream);    
    fclose($errorStream);
    fclose($startRsyncd);   
    
    //Завершим подключение к серверу
    ssh2_exec($connection, 'exit', $pty);
    unset($serverName, $portNumber, $login, $passwd, $command, $pty, $connection );
}


