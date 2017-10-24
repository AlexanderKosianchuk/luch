<?php

namespace Component;

use \Framework\Application as App;

class BaseComponent
{
    protected function em($db = 'default') {
        return App::em($db);
    }

    protected function connection() {
        return App::connection();
    }

    protected function user() {
        return App::user();
    }

    protected function member() {
        return App::dic()->get('user');
    }

    protected function rbac() {
        return App::rbac();
    }

    protected function params() {
        return App::params();
    }
}
