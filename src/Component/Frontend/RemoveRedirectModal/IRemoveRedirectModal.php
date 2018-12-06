<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RemoveRedirectModal;


interface IRemoveRedirectModal
{
    /**
     * @return RemoveRedirectModal
     */
    public function create() : RemoveRedirectModal;
}