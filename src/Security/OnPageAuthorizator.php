<?php
/**
 * Copyright (c) 2019 Wakers.cz
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 */


namespace Wakers\OnPageModule\Security;


use Wakers\BaseModule\Builder\AclBuilder\AuthorizatorBuilder;
use Wakers\UserModule\Security\UserAuthorizator;


class OnPageAuthorizator extends AuthorizatorBuilder
{
    const
        RES_MODULE = 'ONPAGE_RES_MODULE',                   // Celý modul
        RES_PRIMARY = 'ONPAGE_RES_PRIMARY',                 // Nastavení pro vyhledávače
        RES_SOCIAL = 'ONPAGE_RES_SOCIAL',                   // Nastavení pro sociální sítě
        RES_REDIRECT = 'ONPAGE_RES_REDIRECT',               // Přesměrování URL
        RES_REDIRECT_FORM = 'ONPAGE_RES_REDIRECT_FORM',     // Přesměrování URL - Formulář
        RES_REDIRECT_REMOVE = 'ONPAGE_RES_REDIRECT_REMOVE'  // Přesměrování URL - Odstranění
    ;


    public function create() : array
    {
        /*
         * Resources
         */
        $this->addResource(self::RES_MODULE);
        $this->addResource(self::RES_PRIMARY);
        $this->addResource(self::RES_SOCIAL);
        $this->addResource(self::RES_REDIRECT);
        $this->addResource(self::RES_REDIRECT_FORM);
        $this->addResource(self::RES_REDIRECT_REMOVE);


        /*
         * Privileges
         */
        $this->allow([
            UserAuthorizator::ROLE_EDITOR
        ], [
            self::RES_MODULE,
            self::RES_PRIMARY,
            self::RES_SOCIAL,
            self::RES_REDIRECT,
            self::RES_REDIRECT_FORM,
            self::RES_REDIRECT_REMOVE,
        ]);


        return parent::create();
    }
}