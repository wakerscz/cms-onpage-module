<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RemoveRedirectModal;


trait Create
{
    /**
     * @var IRemoveRedirectModal
     * @inject
     */
    public $IOnPage_RemoveRedirectModal;


    /**
     * Modální okno pro odstranění redirectu
     * @return RemoveRedirectModal
     */
    protected function createComponentOnPageRemoveRedirectModal() : object
    {
        $control = $this->IOnPage_RemoveRedirectModal->create();

        $control->onRemove[] = function ()
        {
            $this->getComponent('onPageRedirectModal')->redrawControl('redirectSummary');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}