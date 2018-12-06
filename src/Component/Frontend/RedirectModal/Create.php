<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RedirectModal;


trait Create
{
    /**
     * @var IRedirectModal
     * @inject
     */
    public $IOnPage_RedirectModal;


    /**
     * Modální okno pro nastavení přesměrování starých odkazů
     * @return RedirectModal
     */
    protected function createComponentOnPageRedirectModal() : object
    {
        $control = $this->IOnPage_RedirectModal->create();

        $control->onSave[] = function () use ($control)
        {
            $control->redrawControl('redirectForm');
            $control->redrawControl('redirectSummary');
        };

        $control->onEdit[] = function () use ($control)
        {
            $control->redrawControl('redirectForm');
        };

        return $control;
    }
}