<?php

namespace Component;

use Exception;

class TemplateEngine extends BaseComponent
{
    private $_mustache;

    public function render($tpl, $args)
    {
        if (is_null($this->_mustache)) {
            $this->_mustache = new \Mustache_Engine;
        }

        return $this->_mustache->render($tpl, $args);
    }
}
