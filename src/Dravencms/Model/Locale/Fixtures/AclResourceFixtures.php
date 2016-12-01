<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Dravencms\Model\User\Entities\AclResource;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AclResourceFixtures extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $resources = [
            'locale' => 'Locale'
        ];
        foreach ($resources AS $resourceName => $resourceDescription)
        {
            $aclResource = new AclResource($resourceName, $resourceDescription);
            $manager->persist($aclResource);
            $this->addReference('user-acl-resource-'.$resourceName, $aclResource);
        }
        $manager->flush();
    }
}