<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Dravencms\Model\User\Entities\AclResource;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class AclResourceFixtures extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if (!class_exists(AclResource::class)) {
            trigger_error('dravencms/user module not found, dravencms/locale module won\'t install ACL Resource', E_USER_NOTICE);
            return;
        }

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