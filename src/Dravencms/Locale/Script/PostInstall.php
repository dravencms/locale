<?php

namespace Dravencms\Locale\Script;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Dravencms\Model\User\Repository\AclOperationRepository;
use Dravencms\Model\User\Repository\AclResourceRepository;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PostInstall implements IScript
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var AclResourceRepository */
    private $aclResourceRepository;

    /** @var AclOperationRepository */
    private $aclOperationRepository;

    /**
     * PostInstall constructor.
     * @param MenuRepository $menuRepository
     * @param EntityManager $entityManager
     * @param AclResourceRepository $aclResourceRepository
     * @param AclOperationRepository $aclOperationRepository
     */
    public function __construct(MenuRepository $menuRepository, EntityManager $entityManager, AclResourceRepository $aclResourceRepository, AclOperationRepository $aclOperationRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->aclResourceRepository = $aclResourceRepository;
        $this->aclOperationRepository = $aclOperationRepository;
    }

    /**
     * @param IPackage $package
     * @throws \Exception
     */
    public function run(IPackage $package)
    {
        if (!$aclResource = $this->aclResourceRepository->getOneByName('locale')) {
            $aclResource = new AclResource('locale', 'Issue');

            $this->entityManager->persist($aclResource);
        }

        if (!$aclOperationEdit = $this->aclOperationRepository->getOneByName('edit')) {
            $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of Locale');
            $this->entityManager->persist($aclOperationEdit);
        }

        if (!$aclOperationDelete = $this->aclOperationRepository->getOneByName('delete')) {
            $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of Locale');
            $this->entityManager->persist($aclOperationDelete);
        }

        if (!$aclOperationCurrencyEdit = $this->aclOperationRepository->getOneByName('currencyEdit')) {
            $aclOperationCurrencyEdit = new AclOperation($aclResource, 'currencyEdit', 'Allows editation of Currency');
            $this->entityManager->persist($aclOperationCurrencyEdit);
        }

        if (!$aclOperationCurrencyDelete = $this->aclOperationRepository->getOneByName('currencyDelete')) {
            $aclOperationCurrencyDelete = new AclOperation($aclResource, 'currencyDelete', 'Allows deletion of Currency');
            $this->entityManager->persist($aclOperationCurrencyDelete);
        }

        $configurationMenu = $this->menuRepository->getOneByName('Configuration');
        if (!$configurationMenu)
        {
            $configurationMenu = new Menu('Configuration', null, 'fa-cog');
            $this->entityManager->persist($configurationMenu);
        }

        if (!$this->menuRepository->getOneByPresenter(':Admin:Locale:Locale')) {
            $adminMenu = new Menu('Locale', ':Admin:Locale:Locale', 'fa-language', $aclOperationEdit);
            $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);
        }

        if (!$this->menuRepository->getOneByPresenter(':Admin:Locale:Currency')) {
            $adminMenu = new Menu('Currency', ':Admin:Locale:Currency', 'fa-usd', $aclOperationCurrencyEdit);
            $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);
        }

        $this->entityManager->flush();
    }
}