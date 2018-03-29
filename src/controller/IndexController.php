<?php

namespace Controller;

use \L;

class IndexController extends BaseController
{
  public function indexAction()
  {
    $te = $this->dic('TemplateEngine');
    $tpl = file_get_contents(SITE_ROOT_DIR . '/src/view/index.mustache');

    return $te->render($tpl, [
      'title' => L::title,
      'text' => L::text,
    ]);
  }
}
