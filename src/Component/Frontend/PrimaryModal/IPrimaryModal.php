<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\PrimaryModal;


interface IPrimaryModal
{
    /**
     * @return PrimaryModal
     */
    public function create() : PrimaryModal;
}