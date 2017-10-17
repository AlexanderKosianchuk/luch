<?php

namespace Controller;

use \Framework\Application as App;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $user = App::user();
        $te = App::dic()->get('TemplateEngine');
        $tpl = file_get_contents(SITE_ROOT_DIR . '/back/view/index.mustache');

        return $te->render($tpl, [
            'languange' => $user->getLang(),
            'login' => $user->getLogin(),
            'title' => 'title',
            'script' => $this->gutScriptName()
        ]);
    }

    private function gutScriptName()
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

        return $scriptName;
    }
}
