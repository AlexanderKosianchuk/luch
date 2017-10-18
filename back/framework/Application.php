<?php

namespace Framework;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use DI\ContainerBuilder;

use Exception;

class Application
{
    private static $_em;
    private static $_dic;
    private static $_user;
    private static $_params;
    private static $_acl;
    private static $_rbac;
    private static $_connect;
    private static $_i18n;

    private static $_instance;

    private function __construct() {}

    protected function __clone() {}

    public static function config($cfg)
    {
        if (empty($cfg)) {
            throw new Exception('Config is not set', 1);
        }

        self::configureParams($cfg);
        self::configureDoctrine($cfg);
        self::configureDependencyInjection($cfg);
        self::configureCurrentUser();
        self::configureAcl($cfg);
        self::configureConnectionFactory($cfg);
        self::configureI18n($cfg);
    }

    private static function configureDoctrine($cfg)
    {
        if (!isset($cfg['db'])
            || !isset($cfg['db']['default'])
            || !isset($cfg['db']['fdrs'])
            || !isset($cfg['db']['flights'])
        ) {
            throw new Exception('Config file does not contain doctrine databases config', 1);
        }

        // Create a simple 'default' Doctrine ORM configuration for Annotations
        $anotationConfig = Setup::createAnnotationMetadataConfiguration(
            [SITE_ROOT_DIR.'/entity'],
            true
        );

        // obtaining the entity manager
        foreach ($cfg['db'] as $key => $item) {
            $em = EntityManager::create(
                $item,
                $anotationConfig
            );

            $em->getConfiguration()->addEntityNamespace('Entity', 'Entity');

            self::$_em[$key] = $em;
        }
    }

    private static function configureDependencyInjection($cfg)
    {
        if (!isset($cfg['components'])) {
            throw new Exception('Config file does not contain components config', 1);
        }

        $builder = new ContainerBuilder();
        $builder->addDefinitions($cfg['components']);
        $builder->useAnnotations(true);
        self::$_dic = $builder->build();
    }

    private static function configureCurrentUser()
    {
        $user = self::dic()->get('user');
        $userId = $user->tryAuth($_SESSION, $_COOKIE);

        if ($userId === null) {
            self::$_user = new \Entity\User;
            return;
        }

        self::$_user = self::em()->find('Entity\User', $userId);
    }

    private static function configureParams($cfg)
    {
        if (!isset($cfg['params'])) {
            throw new Exception('Config file does not contain params config', 1);
        }

        self::$_params = $cfg['params'];
    }

    private static function buildAclTree($aclCfg, $role, &$actions)
    {
        foreach ($role['actions'] as $action) {
            if (!in_array($action, $actions)) {
                $actions[] = $action;
            }
        }

        if (isset($role['parent'])
            && isset($aclCfg[$role['parent']])
        ) {
            self::buildAclTree(
                $aclCfg,
                $aclCfg[$role['parent']],
                $actions
            );
        }

        return $actions;
    }

    private static function configureAcl($cfg)
    {
        if (!isset($cfg['acl'])) {
            throw new Exception('Config file does not contain acl config', 1);
        }

        $tree = [];
        $actions = [];
        foreach ($cfg['acl'] as $key => $item) {
            $tree[$key] = self::buildAclTree(
                $cfg['acl'],
                $item,
                $actions
            );
        }

        $allActions = [];
        foreach ($tree as $branch) {
            foreach ($branch as $item) {
                if (!in_array($item, $allActions)) {
                    $allActions[] = $item;
                }
            }
        }

        self::$_acl = [
            'tree' => $tree,
            'actions' => $allActions
        ];

        $dic = self::dic();
        $Rbac = $dic->get('Rbac');
        $role = self::user()->getRole();

        $Rbac->init(self::$_acl, $role);
        self::$_rbac = $Rbac;
    }

    private static function configureConnectionFactory($cfg)
    {
        if (!isset($cfg['db'])) {
            throw new Exception('Config file does not contain database config', 1);
        }

        self::$_connect = self::dic()->get('RealConnection');
        self::$_connect->init($cfg['db']);
    }

    private static function configureI18n($cfg)
    {
        if (!isset($cfg['i18n'])) {
            throw new Exception('Config file does not contain i18n config', 1);
        }

        if (!file_exists($cfg['i18n']['langCache'])) {
            mkdir($cfg['i18n']['langCache'], 0755, true);
        }

        self::$_i18n = new \i18n(
            $cfg['i18n']['langFilesDir'] . '{LANGUAGE}.ini',
            $cfg['i18n']['langCache'],
            $cfg['i18n']['forcedLang']
        );

        self::$_i18n->init();
    }

    private static function app()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function em($db = 'default')
    {
        $instance = self::app();
        return $instance::$_em[$db];
    }

    public static function dic()
    {
        $instance = self::app();
        return $instance::$_dic;
    }

    public static function user()
    {
        $instance = self::app();
        return $instance::$_user;
    }

    public static function params()
    {
        $instance = self::app();
        return $instance::$_params;
    }

    public static function rbac()
    {
        $instance = self::app();
        return $instance::$_rbac;
    }

    public static function connection()
    {
        $instance = self::app();
        return $instance::$_connect;
    }

    public static function i18n()
    {
        $instance = self::app();
        return $instance::$_i18n;
    }
}
