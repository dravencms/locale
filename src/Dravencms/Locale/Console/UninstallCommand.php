<?php

namespace Dravencms\Locale\Console;

use Dravencms\Model\User\Repository\AclResourceRepository;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class UninstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('dravencms:locale:uninstall')
            ->setDescription('Uninstalls dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getHelper('container')->getByType('Kdyby\Doctrine\EntityManager');
        
        /** @var AclResourceRepository $aclResourceRepository */
        $aclResourceRepository = $this->getHelper('container')->getByType('Dravencms\Model\User\Repository\AclResourceRepository');

        try {

            $aclResource = $aclResourceRepository->getOneByName('locale');

            foreach($aclResource->getAclOperations() AS $aclOperation)
            {
                foreach ($aclOperation->getGroups() AS $group)
                {
                    $aclOperation->removeGroup($group);
                }

                foreach ($aclOperation->getAdminMenus() AS $adminMenu)
                {
                    $entityManager->remove($adminMenu);
                }

                $entityManager->remove($aclOperation);
            }

            $entityManager->remove($aclResource);
            $entityManager->flush();


            $output->writeLn('Module uninstalled successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}