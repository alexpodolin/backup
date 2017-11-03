<?php
//Подключим БД
require_once("sql/connect.php");

//Подключим файл с sql запросами
require_once("sql/query.php");

//Подключим файл в кот. описана установка rsync 
require_once("inc/install_daemon.php");

//Завершим подключение к БД
$conn = null;

?>

<!DOCTYPE HTML>
<html lang=ru>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Система  управленмя бэкапами СПБ ГУП АТС Смольного</title>

	<!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Nicolas Gallagher Jonathan Neal normalize.css v4.1.1 -->
    <link href="css/normalize.css" rel="stylesheet">  

    <!-- GUP ATS css -->
    <link href="css/style.css" rel="stylesheet">  

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    
    <!-- Моё -->
    <script src="js/js.js"></script>
</head>

<body>
<!-- HTML-код модального окна для авторизации -->
<div id="authModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <!-- Заголовок модального окна -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Подключение к <span id="serverToConn"></span></h4>
            </div>
            <!-- Основное содержимое модального окна -->
            <div class="modal-body">
              <div id="#" role="form" title="Авторизация для работы на сервере">
                  <form id="authForm" action="#" autocomplete="on" method="POST" name="authForm" role="form">
                      
                      <div class="form-group">                         
                        <input type="hidden" name="serverName" class="form-control" id="serverToEdit" value="">                      
                      </div>
                      
                      <div class="form-group">                                                
                        <input type="text" autocomplete="on" id="authLogin" placeholder="Логин" required class="form-control">
                      </div>

                      <div class="form-group">
                        <input type="password" autocomplete="on" id="authPasswd" placeholder="Пароль" required class="form-control">
                      </div> 

                      <div class="form-group">                               					
                        <input type="text" autocomplete="on" maxlength="5" id="portNumber" placeholder="Номер порта" value="22" required class="form-control">					
                      </div>     
                  </form>
              </div> 
            </div>
            <!-- Футер модального окна -->
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary" id="connectToServer" form="authForm" onclick="connToEditSrv(); return false">Подключиться</button>
              <!-- <input type="submit" class="btn btn-success" id="connectToServer" value="Подключиться"> -->  
              <!-- <button type="button" class="btn btn-success" id="connectToServer">Подключиться</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button> -->
            </div>                 
        </div>
    </div>
</div>

<!--HTML-код модального окна для редактирования конфига-->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!--Заголовок модального окна для редактирования-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Редактирование файла конфигурации сервера <span id="configToEdit"></span></h4>
            </div>
            
            <!-- Основное содержимое модального окна -->
            <div class="modal-body" id="editConfModal" title="Редактирование конфига сервера">
                <form id="editFileForm" method="POST">
                    <!-- <textarea class="form-control" id="editFile"></textarea> -->   
                </form>
            </div>
            
            <!--Футер модального окна-->
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary" id="saveFile" form="editFileForm" onclick="saveToEditConf(); return false">Сохранить</button>            
              <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
            </div>
            
        </div>        
    </div>
</div>

<div class="container-fluid main">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-2">
                    <div id="edit-form"  role="form" title="Добавление и редактирование экземпляров бэкапа">
                        <form action="#" autocomplete="on" method="POST" name="find-form" role="form">
                            <h4>Добавление/Редактирование экземпляра бэкапа</h4> 
                            <div class="form-group">                   
                                <label>Добавить/Редактировать имя сервера для бэкапа:</label>				
                                <input type="text" autocomplete="on" name="srvname" placeholder="Имя сервера" required value="<?=$servername?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Выберите тип бэкапа:</label>					
                                <select name='bkptype' class="form-control"><?=$options?></select>					
                            </div>

                            <div class="form-group">
                                <label>Что бэкапим:</label>				
                                <input type="text" autocomplete="on" name="bkpname" placeholder="Экземпляр бэкапа" required value="<?=$bkpinstance?>" class="form-control">		
                            </div>

                            <div class="form-group">
                                <label>Куда бэкапим:</label>				
                                <input type="text" autocomplete="on" name="bkpdir" placeholder="/backup/Имя Сервера/Тип бэкапа" required value="<?=$bkpdir?>" class="form-control">		
                            </div>		

                            <div class="form-group">
                                <label>Параметры бэкапа:</label>				
                                <input id="submit" type="text" autocomplete="on" name="bkpparam" placeholder="-a" value="<?=$bkpparam?>" class="form-control">
                            </div>	                                            
                           
                            </br>                            
                            <div class="form-group">										
                                <input class="btn btn-default btn-block" name="senddata" type="submit" value="<?=$buttonvalue?>">	
                            </div>
                        </form>
                    </div>
                    
                    <br>
                    <hr>                    
                    
                    <div id="install-form"  role="form" title="Добавление и редактирование экземпляров бэкапа">
                        <form action="#" autocomplete="on" method="POST" name="install-form" role="form">
                            <h4>Установка бэкап агента на удаленный сервер</h4>
                            <div class="form-group">                                					
                                <input type="text" name="Server_name" placeholder="ip-адрес или dns-имя" required class="form-control">					
                            </div>                        
                                                        
                            <div class="form-group">                               
                                <input type="text" autocomplete="on" name="loginname" placeholder="Имя учетной записи" required value="" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <input type="password" name="password" placeholder="Пароль учетной записи" required class="form-control">
                            </div>   
                            
                            <div class="form-group">                               					
                                <input type="text" autocomplete="on" maxlength="5" name="portnumber"  placeholder="Номер порта" value="22" required class="form-control">					
                            </div>
                            
                            <div class="form-group">										
                                <input class="btn btn-default btn-block" id="loading-example-btn" name="connect" type="submit" value="Установка на сервер">	
                            </div>
                        </form>                        
                    </div>
                </div>   
                    
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-responsive table-hover">
                                <thead class="text-nowrap">
                                    <tr>
                                        <th>Имя Сервера</th>
                                        <th>Тип бэкапа</th>
                                        <th>Имя экземпляра бэкапа</th>
                                        <th>Куда бэкапим</th>
                                        <th>Параметры бэкапа</th>                                        
                                        <th>Редактирование</th>
                                        <th>Удаление</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <?=$table?>
                            </table>
                        </div>						
                    </div>
                </div>
                
            </div>	
        </div>       
    </div>	   
</div>        
    
</body>
</html>