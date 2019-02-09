<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Manager;


use Nette\Http\FileUpload;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\Exception\ProtectedFileException;
use Wakers\BaseModule\Util\ProtectedFile;
use Wakers\OnPageModule\Database\Primary;
use Wakers\OnPageModule\Database\Redirect;
use Wakers\OnPageModule\Database\Social;
use Wakers\OnPageModule\Repository\OnPageRepository;
use Wakers\PageModule\Database\Page;


class OnPageManager extends AbstractDatabase
{
    /**
     * @var OnPageRepository
     */
    protected $onPageRepository;


    /**
     * OnPageManager constructor.
     * @param OnPageRepository $onPageRepository
     */
    public function __construct(OnPageRepository $onPageRepository)
    {
        $this->onPageRepository = $onPageRepository;
    }


    /**
     * @param Redirect|NULL $redirect
     * @param Page $page
     * @param string $oldUrl
     * @throws DatabaseException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveRedirect(?Redirect $redirect, Page $page, string  $oldUrl) : void
    {
        $redirectByUrl = $this->onPageRepository->findOneRedirectByOldUrl($oldUrl);

        if (($redirectByUrl && $redirect !== $redirectByUrl) || (!$redirect && $redirectByUrl))
        {
            throw new DatabaseException("Původní URL '{$oldUrl}' již existuje.");
        }

        if ($redirect === NULL)
        {
            $redirect = new Redirect;
        }

        $redirect->setPage($page);
        $redirect->setOldUrl($oldUrl);
        $redirect->save();
    }


    /**
     * @param Page $page
     * @param string $title
     * @param string|null $description
     * @param int $indexingType
     * @param bool $isCanonical
     * @throws DatabaseException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function savePrimary(
        Page $page,
        string $title,
        string $description = NULL,
        int $indexingType = Primary::INDEXING_TYPE_INDEX_FOLLOW,
        bool $isCanonical = FALSE
    )
    : void
    {
        $onPagePrimary = $page->getPrimary();

        if ($onPagePrimary === NULL)
        {
            $onPagePrimary = new Primary;
            $onPagePrimary->setPage($page);
        }

        $onPagePrimaryByTitle = $this->onPageRepository->findOnePrimaryByTitle($title);

        if ($onPagePrimaryByTitle && $onPagePrimaryByTitle !== $onPagePrimary)
        {
            throw new DatabaseException("Stránka s titulkem '{$title}' již existuje.");
        }

        $onPagePrimary->setTitle($title);
        $onPagePrimary->setDescription($description);
        $onPagePrimary->setIndexingType($indexingType);
        $onPagePrimary->setCanonical($isCanonical);

        $onPagePrimary->save();
    }


    /**
     * @param Page $page
     * @param string|NULL $title
     * @param string|NULL $description
     * @throws DatabaseException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveSocial(Page $page, string $title, string $description = NULL) : void
    {
        $onPageSocial = $page->getSocial();

        if ($onPageSocial === NULL)
        {
            $onPageSocial = new Social;
            $onPageSocial->setPage($page);
        }

        $onPageSocialByTitle = $this->onPageRepository->findOneSocialByTitle($title);

        if ($onPageSocialByTitle && $onPageSocialByTitle !== $onPageSocial)
        {
            throw new DatabaseException("Titulek pro soc. sítě '{$title}' již existuje.");
        }

        $onPageSocial->setTitle($title);

        if ($description)
        {
            $onPageSocial->setDescription($description);
        }

        $onPageSocial->save();
    }


    /**
     * @param Page $page
     * @param FileUpload $image
     * @param bool $override
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws ProtectedFileException
     */
    public function saveSocialImage(Page $page, FileUpload $image) : void
    {
        $onPageSocial = $page->getSocial();

        // Odstraní aktuální soubor
        if ($onPageSocial->getImageName())
        {
            $pf = new ProtectedFile(OnPageRepository::DESTINATION_SOCIAL_IMAGE, $onPageSocial->getImageName());
            $pf->remove();
        }

        $pf = new ProtectedFile(OnPageRepository::DESTINATION_SOCIAL_IMAGE, NULL);
        $name = $pf->move($image);

        $onPageSocial->setImageName($name);

        $onPageSocial->save();
    }


    /**
     * @param Redirect $redirect
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function removeRedirect(Redirect $redirect) : void
    {
        $redirect->delete();
    }


    /**
     * @param Page $page
     * @return bool TRUE pokud je soubor odstraněn
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function removeSocialImage(Page $page) : bool
    {
        if ($onPageSocial = $page->getSocial())
        {
            $pf = new ProtectedFile(OnPageRepository::DESTINATION_SOCIAL_IMAGE, $onPageSocial->getImageName());

            if ($pf->remove())
            {
                $onPageSocial->setImageName(NULL);
                $onPageSocial->save();

                return TRUE;
            }
        }

        return FALSE;
    }
}