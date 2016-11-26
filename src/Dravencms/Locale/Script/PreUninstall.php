<?php
namespace Dravencms\Locale\Script;

use Dravencms\Model\User\Repository\AclResourceRepository;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PreUninstall implements IScript
{
    /** @var AclResourceRepository */
    private $aclResourceRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * PreUninstall constructor.
     * @param EntityManager $entityManager
     * @param AclResourceRepository $aclResourceRepository
     */
    public function __construct(EntityManager $entityManager, AclResourceRepository $aclResourceRepository)
    {
        $this->entityManager = $entityManager;
        $this->aclResourceRepository = $aclResourceRepository;
    }

    /**
     * @param IPackage $package
     * @throws \Exception
     */
    public function run(IPackage $package)
    {
        $aclResource = $this->aclResourceRepository->getOneByName('locale');

        foreach($aclResource->getAclOperations() AS $aclOperation)
        {
            foreach ($aclOperation->getGroups() AS $group)
            {
                $aclOperation->removeGroup($group);
            }

            foreach ($aclOperation->getAdminMenus() AS $adminMenu)
            {
                $this->entityManager->remove($adminMenu);
            }

            $this->entityManager->remove($aclOperation);
        }

        $this->entityManager->remove($aclResource);
        $this->entityManager->flush();
    }
}