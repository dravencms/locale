<?php

namespace Dravencms\Locale\Console;

use App\Model\Admin\Entities\Menu;
use App\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('dravencms:locale:install')
            ->setDescription('Installs dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MenuRepository $adminMenuRepository */
        $adminMenuRepository = $this->getHelper('container')->getByType('App\Model\Admin\Repository\MenuRepository');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getHelper('container')->getByType('Kdyby\Doctrine\EntityManager');

        try {

            $aclResource = new AclResource('locale', 'Issue');

            $entityManager->persist($aclResource);

            $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of Locale');
            $entityManager->persist($aclOperationEdit);
            $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of Locale');
            $entityManager->persist($aclOperationDelete);

            $aclOperationCurrencyEdit = new AclOperation($aclResource, 'currencyEdit', 'Allows editation of Currency');
            $entityManager->persist($aclOperationCurrencyEdit);
            $aclOperationCurrencyDelete = new AclOperation($aclResource, 'currencyDelete', 'Allows deletion of Currency');
            $entityManager->persist($aclOperationCurrencyDelete);

            $configurationMenu = $adminMenuRepository->getOneByName('Configuration');
            if (!$configurationMenu)
            {
                $configurationMenu = new Menu('Configuration', null, 'fa-cog');
                $entityManager->persist($configurationMenu);
            }
            
            $adminMenu = new Menu('Locale', ':Admin:Locale:Locale', 'fa-language', $aclOperationEdit);
            $adminMenuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);

            $adminMenu = new Menu('Currency', ':Admin:Locale:Currency', 'fa-usd', $aclOperationCurrencyEdit);
            $adminMenuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $configurationMenu);

            $output->writeLn('Module installed successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}