<?php

namespace Dravencms\Locale\Script;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PostInstall implements IScript
{
    private $menuRepository;
    private $entityManager;

    public function __construct(MenuRepository $menuRepository, EntityManager $entityManager)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    public function run(IPackage $package)
    {
        $aclResource = new AclResource('locale', 'Issue');

        $this->entityManager->persist($aclResource);

        $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of Locale');
        $this->entityManager->persist($aclOperationEdit);
        $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of Locale');
        $this->entityManager->persist($aclOperationDelete);

        $aclOperationCurrencyEdit = new AclOperation($aclResource, 'currencyEdit', 'Allows editation of Currency');
        $this->entityManager->persist($aclOperationCurrencyEdit);
        $aclOperationCurrencyDelete = new AclOperation($aclResource, 'currencyDelete', 'Allows deletion of Currency');
        $this->entityManager->persist($aclOperationCurrencyDelete);

        $configurationMenu = $this->menuRepository->getOneByName('Configuration');
        if (!$configurationMenu)
        {
            $configurationMenu = new Menu('Configuration', null, 'fa-cog');
            $this->entityManager->persist($configurationMenu);
        }

        $adminMenu = new Menu('Locale', ':Admin:Locale:Locale', 'fa-language', $aclOperationEdit);
        $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);

        $adminMenu = new Menu('Currency', ':Admin:Locale:Currency', 'fa-usd', $aclOperationCurrencyEdit);
        $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);

    }
}