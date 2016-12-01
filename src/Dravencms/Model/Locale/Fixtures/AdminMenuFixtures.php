<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Dravencms\Model\Admin\Entities\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AdminMenuFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Locale
        $menu = $manager->getRepository(Menu::class);
        // Locale
        $root = new Menu('Locale', null, 'fa-language', $this->getReference('user-acl-operation-locale-edit'), null);
        $manager->persist($root);
        $child = new Menu('Locale', ':Admin:Locale:Locale', 'fa-language', $this->getReference('user-acl-operation-locale-edit'));
        $manager->persist($child);
        $menu->persistAsLastChildOf($child, $root);
        $child = new Menu('Currency', ':Admin:Locale:Currency', 'fa-usd', $this->getReference('user-acl-operation-locale-currencyEdit'));
        $manager->persist($child);
        $menu->persistAsLastChildOf($child, $root);
        $manager->flush();
    }
    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getDependencies()
    {
        return ['Dravencms\Model\Locale\Fixtures\AclOperationFixtures'];
    }
}