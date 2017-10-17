<?php

namespace Component;

use \Framework\Application as App;

class BaseComponent
{
    protected function em() {
        return App::em();
    }

    protected function user() {
        return App::user();
    }
}
