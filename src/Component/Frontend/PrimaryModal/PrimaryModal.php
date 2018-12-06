<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\OnPageModule\Component\Frontend\PrimaryModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\LangModule\Translator\Translate;
use Wakers\OnPageModule\Database\Primary;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class PrimaryModal extends BaseControl
{
    use AjaxValidate;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Page
     */
    protected $activePage;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * PrimaryModal constructor.
     * @param OnPageManager $onPageManager
     * @param PageRepository $pageRepository
     * @param Translate $translate
     */
    public function __construct(
        OnPageManager $onPageManager,
        PageRepository $pageRepository,
        Translate $translate
    ) {
        $this->onPageManager = $onPageManager;
        $this->translate = $translate;

        $this->activePage = $pageRepository->getActivePage();
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/primaryModal.latte');
        $this->template->render();
    }


    /**
     * Primary form
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentPrimaryForm() : Form
    {
        $indexingTypes = [
            Primary::INDEXING_TYPE_INDEX_FOLLOW     => 'Indexovat, následovat odkazy',
            Primary::INDEXING_TYPE_INDEX_NOFOLLOW   => 'Indexovat, nenásledovat odkazy',
            Primary::INDEXING_TYPE_NOINDEX_FOLLOW   => 'Neindexovat, následovat odkazy',
            Primary::INDEXING_TYPE_NOINDEX_NOFOLLOW => 'Neindexovat, nenásledovat odkazy'
        ];

        $canonicalTypes = [
            0 => 'Stránka není kanonická',
            1 => 'Stránka je kanonická'
        ];


        $form = new Form;

        $form->addText('title')
            ->setRequired('Page title is required.')
            ->addRule(Form::MIN_LENGTH, 'Minimal length of page title is %d chars.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximal length of page title is %d chars.', 100);

        $form->addText('description')
            ->setRequired(FALSE)
            ->addRule(Form::MIN_LENGTH, 'Minimal length of page title is %d chars.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximal length of page title is %d chars.', 200);

        $form->addSelect('indexingType', NULL, $indexingTypes)
            ->setRequired('Type of indexing is required.');

        $form->addSelect('isCanonical', NULL, $canonicalTypes)
            ->setRequired('Type of canonization is required.');

        $form->addSubmit('save');


        $onPagePrimary = $this->activePage->getPrimary();

        if ($onPagePrimary)
        {
            $form->setDefaults([
                'title' => $onPagePrimary->getTitle(),
                'description' => $onPagePrimary->getDescription(),
                'indexingType' => $onPagePrimary->getIndexingType(),
                'isCanonical' => (int) $onPagePrimary->getCanonical()
            ]);
        }


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * Success
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            try
            {
                $this->onPageManager->savePrimary($this->activePage, $values->title, $values->description, $values->indexingType, $values->isCanonical);

                $this->presenter->notificationAjax(
                    $this->translate->translate('Page updated'),
                    $this->translate->translate('On-page factors has been successfully updated.'),
                    'success',
                    FALSE
                );

                $this->onSave();
            }
            catch (DatabaseException $exception)
            {
                $this->presenter->notificationAjax(
                    $this->translate->translate('Error'),
                    $exception->getMessage(),
                    'error'
                );
            }
        }
    }
}