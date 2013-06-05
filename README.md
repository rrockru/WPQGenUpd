WPQGenUpd
-------------------
Плагин Wordpress для создания зеркала обновлений редактора игр QGEN для платформы QSP.

Установка
-------------------
1. Папку qgenupdates скопировать в папку плагинов Wordpress (wp-content/plugins).
2. Папку QGen скопировать в директорию, доступную через браузер.
3. Прописать mime-type .ver для обработки через PHP:  
    `application/x-httpd-php = .php .php3 .php4 .php5 .phtml .ver`
4. В файле QGen.ver поправить путь до файла wp-load.php:  
    `require_once('../wp-load.php');`
5. В админке Wordpress активировать плагин "QGen Updates".
6. Файлы QGen'a закачивать в ту же папку, куда закчали QGen.ver.

Работа с плагином
-------------------
При активации плагина в базе Wordpress'a создается таблица "qgenupdates", в которой хранится информация об обновлении.  
Для редактировании версий обновления необходимо в админке Wordpress'a открыть "Инструменты->Обновления QGen".  
Сразу после активации там будет находиться только одна запись - QGen 5.0.0.  
Работа с плагином схожа с обычным добавлением страниц/записей.  
При запросе QGen.ver из базы будет извлекаться запись с самой новой версией QGen.  
При удалении плагина удаляется и таблица "qgenupdates".