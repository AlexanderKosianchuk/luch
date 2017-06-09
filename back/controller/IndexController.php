<?php

namespace Controller;

use Model\UserOptions;
use Model\Language;

class IndexController extends CController
{
    public $curPage = 'indexPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
    }

    public function getUserLanguage()
    {
        return $this->userLang;
    }

    public function getAvaliableLanguages()
    {
        return implode(',', Language::getAvaliableLanguages());
    }

    public function getUserLogin()
    {
        return $this->_user->username;
    }

    public function PutHelpDialog()
    {
        $version = '1.01';
        if(defined('VERSION')) {
            $version = VERSION;
        }

        printf("<div id='helpDialog' style='display: none;' title='%s'>
                <p>
                </p>
                ===== F5 - Возврат в Главное меню LUCH =====
                </p>
                1. На графике :  V -  установить вертикальное сечение
                </p>
                ** Двойной LeftMouse - на окне времени сечения - отмена
                <p>
                2. На графике :  N - отобразить имена параметров
                </p>
                ** повторное нажатие N - отмена
                <p>
                3. На графике :  L - установить справа от визира имена параметров
                </p>
                ** повторное нажатие L - отмена
                <p>
                4. На графике :  D - равномерное распределение аналоговых параметров на экране
                </p>
                <p>
                5. На графике :  SHFT + D - равномерное распределение линий и разовых команд на экране
                </p>
                <p>
                6. На графике :  LeftMouse - при круглом указателе - выбор линии для редактирования (утолщается)
                </p>
                ** LeftMouse - на круглом указателе - отмена выбора линии
                </p>
                ** Mouse вверх/вниз - на графике : перемещение выбранной линии
                </p>
                ** Ctrl + Mouse вверх/вниз - на графике : увеличение/уменьшение масштаба выбранной линии
                </p>
                <p>
                7. На графике :  Ролик Mouse на себя / от себя - удаление / приближение графика
                </p>
                <p>
                8. На графике :  Двойной LeftMouse - приближение графика по линии визира
                </p>
                <p>
                9. На графике :  + / - на правой панели - увеличение/уменьшение количества горизонтальных линий сетки
                </p>
                <p>
                10. В режиме <Бланк> или <График на печать> : Ctrl + P - вывод на печать (или запись в файл)
                </p>
                <p>
                </p>
                <p>
                Версия: %s
                </p>
                </div>", 'title', $version);
    }

    public function PutScripts()
    {
        $files = scandir ('public/');
        $scriptName = '';
        foreach ($files as $item) {
            $fileParts = pathinfo($item);
            if ((strpos($item, 'index') !== false)
                && ($fileParts['extension'] === 'js')
            ) {
                $scriptName = $item;
            }
        }
        printf("<script type='text/javascript' src='/public/".$scriptName."'></script>");
    }
}
