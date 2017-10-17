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
        self::$_em = EntityManager::create(
            $cfg['db']['default'],
            $anotationConfig
        );

        self::$_em->getConfiguration()->addEntityNamespace('Entity', 'Entity');
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
        $user = self::dic()->get('User');
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

        $Rbac->configAcl(self::$_acl, $role);
        self::$_rbac = $Rbac;
    }

    private static function app()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function em()
    {
        $instance = self::app();
        return $instance::$_em;
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
}
