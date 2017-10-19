<?php

namespace Component;

use \Framework\Application as App;

class BaseComponent
{
    protected function em() {
        return App::em();
    }

    protected function connection() {
        return App::connection();
    }

    protected function user() {
        return App::user();
    }

    protected function rbac() {
        return App::rbac();
    }
}
