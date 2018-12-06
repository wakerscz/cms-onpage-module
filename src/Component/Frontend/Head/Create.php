<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\Head;


trait Create
{
    /**
     * @var IHead
     * @inject
     */
    public $IOnPage_Head;


    /**
     * Komponenta s informacemi pro vyhledávače a sociální sítě
     * @return Head
     */
    protected function createComponentOnPageHead() : object
    {
        return $this->IOnPage_Head->create();
    }
}