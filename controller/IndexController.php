<?php

require_once(@"includes.php");

class IndexController extends CController
{
    public $curPage = 'indexPage';
    public $flightActions;

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
        printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
                <link rel='stylesheet' type='text/css' href='stylesheets/jquery-ui-1.10.3.custom.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/chosen/chosen.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/jsTreeThemes/default/style.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/dataTables/jquery.dataTables.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/jquery.colorpicker.css'/>
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/bruTypeTemplates.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/viewOptionsParams.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/viewOptionsEvents.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/chart.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/user.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/flight.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/searchFlight.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/style.css' />");
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

        printf("<div id='optionsDialog' title='%s'>
                <p><form id='optionsForm'>
                    %s
                </form></p>
                </div>",
                $this->lang->options,
                $optionsStr);
    }

    public function PutExportLink()
    {
        printf("<div id='exportLink'></div>");
    }

    public function PutScripts()
    {
        printf("<script type='text/javascript' src='scripts/include/jquery/jquery-2.1.1.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/jquery/jquery.mousewheel.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/chosen/chosen.proto.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/chosen/chosen.jquery.js'></script>");

        //The jQuery UI widget factory, can be omitted if jQuery UI is already included
        printf("<script type='text/javascript' src='scripts/include/fileUploader/vendor/jquery.ui.widget.js'></script>");
        //The Iframe Transport is required for browsers without support for XHR file uploads
        printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.iframe-transport.js'></script>");
        //jstree
        printf("<script type='text/javascript' src='scripts/include/jstree/jstree.min.js'></script>");
        //tables
        //printf("<script type='text/javascript' src='scripts/include/jtable/jquery.jtable.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/dataTables/jquery.dataTables.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/dataTables/dataTables.fixedHeader.min.js'></script>");

        //colorpicker
        printf("<script type='text/javascript' src='scripts/include/colorpicker/jquery.colorpicker.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-parser.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-crayola.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-memory.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-pantone.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-ral-classic.js'></script>
            <script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-percentage-parser.js'></script>");

        //flot
        printf("<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>-->
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>-->

            <!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->");
        //The basic File Upload plugin
        printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.fileupload.js'></script>");
        //manual scripts
        printf("<script type='text/javascript' src='scripts/index.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/Lang.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/WindowFactory.proto.js'></script>");

        //main
        printf("<script type='text/javascript' src='scripts/proto/main/FlightList.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/main/FlightUploader.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/main/FlightProccessingStatus.proto.js'></script>");

        //bruType
        printf("<script type='text/javascript' src='scripts/proto/bruType/BruType.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeGeneralInfo.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeTemplates.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeEvents.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeCyclo.js'></script>");

        //user
        printf("<script type='text/javascript' src='scripts/proto/user/User.proto.js'></script>");

        //searchFlight
        printf("<script type='text/javascript' src='scripts/proto/searchFlight/SearchFlight.proto.js'></script>");

        //viewOptions
        printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptions.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsTpls.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsEvents.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsParams.js'></script>");


        //chart scripts
        printf("<script type='text/javascript' src='scripts/proto/chart/Chart.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/AxesWorker.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Exception.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Legend.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Param.proto.js'></script>");
    }

    public function PutFooter()
    {
        printf("</body></html>");
    }

}
