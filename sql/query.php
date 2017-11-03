<?php
/* 
Получение списка экземпляров бэкапа
Редактировнаие списка экземпляров бэкапа
Удаление экземпляров бэкапов 
*/

//Получим тип бэкапа из БД
$sql_bkp_type = 'SELECT * FROM backup_type';
foreach ($conn->query($sql_bkp_type) as $row) { {
        $selected = $bkptype != $row[Id_bkp_type] ? "" : "selected=selected";
        $options .= "<option {$selected} value='{$row[Id_bkp_type]}'>{$row[Backup_type]} </option>";
    }
}

//Выведем список сущ. серверов с параметрами бэкапа
$result = 'SELECT s.Server_name, bt.Backup_type, r.Bkp_instance, r.Bkp_dir, r.Bkp_param, r.Id FROM result r JOIN'
        . ' servers s ON s.Id_server=r.Id_server JOIN backup_type bt ON bt.Id_bkp_type=r.Id_bkp_type';
$stmt = $conn->prepare($result);
$stmt->execute();

//Пройдемся по всем строкам таблиц
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
//Определим строку для удаления
$rowdel = "?delete={$row[Id]}";

//Определим строку для редактирования
$rowedit="?edit={$row[Id]}";

//Запишем все в ячейки таблицы
    $table .= "<tr>"
            . "<td id=\"serverName$row[Id]\">{$row[Server_name]}</td>"
            . "<td>{$row[Backup_type]}</td>"
            . "<td>{$row[Bkp_instance]}</td>"
            . "<td>{$row[Bkp_dir]}</td>"
            . "<td>{$row[Bkp_param]}</td>"
//            . "<td><a href='{$rowedit}'>Редактировать</a></td>"
//            . "<td><a class=\"btn btn-warning btn-sm\" href={$rowedit}>Редактировать</a></br><a  href=\"#\" onClick=\"$('#authModal').modal('toggle'); return false;\">dklghfkjdhfg</a><button type=\"button\" data-toggle=\"modal\" data-target=\"#authModal\">Click me</button></td>"
            . "<td><a class=\"btn btn-warning btn-sm\" href={$rowedit}>Редактировать</a></br><a href=\"#authModal\" data-toggle=\"modal\" onClick=\"getSrvNameValue($row[Id])\">конфиг. сервера</a></td>"
//            . "<td><a onclick='return confirm(\"Вы уверены что хотите удалить\") ? true : false;' href='{$rowdel}'>Удалить</a></td>"
            . "<td><a class=\"btn btn-danger btn-sm\" onclick='return confirm(\"Вы уверены что хотите удалить экземпляр $row[Bkp_instance] ?\") ? true : false;' href='{$rowdel}'>Удалить</a></td>"
            . "</tr>";         
}

//Уст. значение кнопки по умолчанию
$buttonvalue = "Сохранить на сервере";

//По нажатию кнопки "Сохранить на сервере"
if ($_POST[senddata]=="Сохранить на сервере")
{
    //Получим id сервера в таблице со списком серверов
    $sql = "SELECT * FROM servers WHERE Server_name='{$_POST[srvname]}'";
    foreach ($conn->query($sql) as $row) 
    {       		
        $serverid = $row[Id_server];		
    }

    //Добавим сервер в таблицу БД если он отсутствует
    if (!$serverid) { 
        $conn->exec("INSERT INTO servers (`Server_name`) VALUES ('{$_POST[srvname]}')");
        $serverid = $conn->lastInsertId();
    }

    //Вставим в БД данные для бэкапа
    $conn->exec("INSERT INTO result (					
        `Id_server`,
        `Id_bkp_type`, 
        `Bkp_instance`,
        `Bkp_dir`,
        `Bkp_param`
        ) 
        VALUES (
            '{$serverid}', 
            '{$_POST[bkptype]}', 
            '{$_POST[bkpname]}', 
            '{$_POST[bkpdir]}', 
            '{$_POST[bkpparam]}'
        )
                            ");
    header("Location: /");
    exit;
}


//Запрос на удаление строки по id сервера
if ($_GET[delete])
{
    $conn->exec("DELETE FROM result WHERE Id=$_GET[delete]");
    $conn->exec("SELECT COUNT(Id_server) FROM result");
    header("Location: /");	
    exit;
}

//Запросы на редактирование
if ($_GET[edit])
{
    //Запрос на подстановку имени сервера в поле input
    foreach ($conn->query("SELECT * FROM servers WHERE Id_server=(SELECT Id_server FROM result WHERE Id=$_GET[edit])") as $row);
        {
            $servername = $row[Server_name];
        }
    //Запрос на подстановку типа бэкапа в поле select
    foreach ($conn->query("SELECT * FROM backup_type WHERE Id_bkp_type=(SELECT Id_bkp_type FROM result WHERE Id=$_GET[edit])") as $row);
        {
            $bkptype = $row[Id_bkp_type];
        }
    //Запрос на подстановку экземпляра бэкапа в поле input
    //Запрос на подстановку пути куда бэкапим в поле input	
    //Запрос на подстановку параметров с которыми бэкапим в поле input
    foreach ($conn->query("SELECT * FROM result WHERE Id=$_GET[edit]") as $row);
        {
            $bkpinstance = $row[Bkp_instance];
            $bkpdir = $row[Bkp_dir];
            $bkpparam = $row[Bkp_param];
        }

    if (isset($_GET[edit]))
        $buttonvalue = "Редактировать";			
}

//Если кнопка редактировать, то обновим таблицу
if ($_POST[senddata]=="Редактировать")	
{
    $conn->exec("UPDATE result SET `Bkp_instance`='{$_POST[bkpname]}', `Bkp_dir`='{$_POST[bkpdir]}', `Bkp_param`='{$_POST[bkpparam]}' WHERE Id='$_GET[edit]'");
    header("Location: /");
    exit;
}

?>