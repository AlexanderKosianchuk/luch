<?php

namespace Controller;

use Model\UserOptions;

class IndexController extends CController
{
    public $curPage = 'indexPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
    }

    public function PutCharset()
    {
        printf("<!DOCTYPE html>
            <html lang='%s'>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>",
                $this->userLang);
    }

    public function PutTitle()
    {
        printf("<title>%s</title>", $this->lang->title);
    }

    public function PutStyleSheets()
    {
        printf("<link href='/front/stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />");
    }

    public function PutHeader()
    {
        printf("</head><body>");
    }

    public function EventHandler()
    {
        printf("<div id='eventHandler'></div>");
    }

    public function PutMessageBox()
    {
        printf("<div id='dialog' title='%s'>
                <p></p>
                </div>", $this->lang->message);
    }

    public function PutHelpDialog()
    {
        printf("<div id='helpDialog' title='%s'>
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
                </div>", $this->lang->helpTitle);
    }

    public function PutOptionsDialog()
    {
        $O = new UserOptions();
        $userInfo = $this->_user->GetUserInfo($this->_user->username);
        $userId = $userInfo['id'];
        $options = $O->GetOptions($userId);
        unset($O);

        $optionsStr = '';
        foreach ($options as $key => $val) {
            $input = sprintf('<input  class="options-value-input" name="%s" value="%s">',
                $key, $val);

            $optionsStr .= sprintf('<div class="options-row">' .
                '<div class="options-name">%s</div>' .
                '<div class="options-value">%s</div>' .
                '<div class="options-clear"></div>' .
                '</div>',
                (isset($this->lang->$key) ? $this->lang->$key : $key),
                $input);
        }

        $version = '1.01';
        if(defined('VERSION')) {
            $version = VERSION;
        }

        printf("<div id='optionsDialog' class='options-dialog' title='%s'>
                <form id='optionsForm'>
                    %s
                </form>
                <p class='options-dialog__version'>Version: %s</p>
                </div>",
                $this->lang->options,
                $optionsStr,
                $version
        );
    }

    public function PutExportLink()
    {
        printf("<div id='exportLink'></div>");
    }

    public function PutScripts()
    {
        printf('<script src="/public/index.js"></script>');
    }

    public function PutFooter()
    {
        printf("</body></html>");
    }

}
