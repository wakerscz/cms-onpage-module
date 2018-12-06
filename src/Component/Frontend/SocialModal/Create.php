<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\SocialModal;


trait Create
{
    /**
     * @var ISocialModal
     * @inject
     */
    public $IOnPage_SocialModal;


    /**
     * Modální okno pro základní nastavení pro sociální sítě
     * @return SocialModal
     */
    protected function createComponentOnPageSocialModal() : object
    {
        $control = $this->IOnPage_SocialModal->create();

        $control->onSave[] = function () use ($control)
        {
            $control->redrawControl('socialForm');
        };

        $control->onRemoveImage[] = function () use ($control)
        {
            $control->redrawControl('socialForm');
        };

        return $control;
    }
}