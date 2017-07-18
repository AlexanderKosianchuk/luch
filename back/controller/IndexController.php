<?php

namespace Controller;

use Model\UserOptions;
use Model\Language;

class IndexController extends CController
{
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
