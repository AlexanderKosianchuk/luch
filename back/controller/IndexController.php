<?php

namespace Controller;

use \L;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $te = $this->dic()->get('TemplateEngine');
        $tpl = file_get_contents(SITE_ROOT_DIR . '/back/view/index.mustache');

        return $te->render($tpl, [
            'languange' => $this->user()->getLang(),
            'login' => $this->user()->getLogin(),
            'role' => $this->user()->getRole(),
            'title' => L::title,
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
