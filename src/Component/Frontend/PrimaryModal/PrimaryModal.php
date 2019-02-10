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
use Wakers\BaseModule\Util\SetDisabledForm;
use Wakers\OnPageModule\Database\Primary;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\OnPageModule\Security\OnPageAuthorizator;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class PrimaryModal extends BaseControl
{
    use AjaxValidate;
    use SetDisabledForm;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Page
     */
    protected $activePage;


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * PrimaryModal constructor.
     * @param OnPageManager $onPageManager
     * @param PageRepository $pageRepository
     */
    public function __construct(
        OnPageManager $onPageManager,
        PageRepository $pageRepository
    ) {
        $this->onPageManager = $onPageManager;
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
            ->setRequired('Titulek stránky je povinný.')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka názvu stránky jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu stránky je %d znaků.', 100);

        $form->addText('description')
            ->setRequired(FALSE)
            ->addRule(Form::MIN_LENGTH, 'Minimální délka popisu stránky jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka popisu stránky je %d znaků.', 200);

        $form->addSelect('indexingType', NULL, $indexingTypes)
            ->setRequired('Typ indexace je povinný.');

        $form->addSelect('isCanonical', NULL, $canonicalTypes)
            ->setRequired('Typ kanonizace je povinný.');

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


        if (!$this->presenter->user->isAllowed(OnPageAuthorizator::RES_PRIMARY))
        {
            $this->setDisabledForm($form);
        }

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
                    'On-Page faktory',
                    'On-Page faktory byly úspěšně upraveny.',
                    'success',
                    FALSE
                );

                $this->onSave();
            }
            catch (DatabaseException $exception)
            {
                $this->presenter->notificationAjax(
                    'Chyba',
                    $exception->getMessage(),
                    'error'
                );
            }
        }
    }
}