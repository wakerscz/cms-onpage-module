<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RedirectModal;


interface IRedirectModal
{
    /**
     * @return RedirectModal
     */
    public function create() : RedirectModal;
}