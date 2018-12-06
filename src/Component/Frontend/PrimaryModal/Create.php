<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\PrimaryModal;


trait Create
{
    /**
     * @var IPrimaryModal
     * @inject
     */
    public $IOnPage_PrimaryModal;


    /**
     * Modální okno se základním nastavením pro sociální sítě
     * @return PrimaryModal
     */
    protected function createComponentOnPagePrimaryModal() : object
    {
        $control = $this->IOnPage_PrimaryModal->create();

        $control->onSave[] = function ()
        {
            $this->getComponent('onPageHead')->redrawControl('title');
        };

        return $control;
    }
}