'use strict';
 
//Получим имя сервера к которому подключаемся при нажатии на ссылку для редактирования
function getSrvNameValue(serverNameId) 
{
    var serverNameValue = document.getElementById('serverName' + serverNameId).innerText; //Находим имя сервера по id
    var serverToConnect = document.getElementById('serverToConn'); //Находим объект в который нужно добавить текст с именем  
    var serverToEdit = document.getElementById('serverToEdit'); //Находим объект в котором получаем имя сервера куда коннектимся
    serverToConnect.innerText =  serverNameValue; // Меняем свойство объекта
    serverToEdit.value = serverNameValue; //Имя сервера куда коннектимся
    
}

//По нажатию "Подключиться" соединимся с сервером, скачаем файл, откроем содержимое в textarea
function connToEditSrv() {
    var srvToEdit = document.getElementById('serverToEdit').value;
    var authLogin = document.getElementById('authLogin').value;
    var authPasswd = document.getElementById('authPasswd').value;
    var portNumber = document.getElementById('portNumber').value;    
    var dataString = 'srvToEdit=' + srvToEdit + '&authLogin=' + authLogin + '&authPasswd=' + authPasswd + '&portNumber=' + portNumber;
    $.ajax({         
        url: 'inc/get_config.php',         
        type: 'POST',
        data: dataString,         
        async: true,        
        success: function(response) {
            var configToEditValue = document.getElementById('serverToEdit').value; //Подстановка значения сервера в название
            configToEdit.innerText = configToEditValue;
            $('#authModal').modal('hide');   //спрячем модальное окно авторизации
            $('#editModal').modal('show'); // Выведем форму для редактирования
            var FileContent = document.getElementById('editFileForm');
            FileContent.innerHTML = '<textarea class="form-control" id="editFile">' + response + '</textarea>'; 
        }
    });
}

//По нажатию "Сохранить" сохраним в файл
function saveToEditConf() {
    var confToSave = document.getElementById('editFile').value;
    var dataString = 'editFile=' + confToSave;    
    $.ajax({
        dataType: 'text',
        url: 'inc/edit_rsync_conf.php',         
        type: 'POST',
        data: dataString,         
        async: true,
        success: function(response) {            
            $('#editModal').modal('hide'); //спрячем модальное окно редактирования            
        }
    });
}





