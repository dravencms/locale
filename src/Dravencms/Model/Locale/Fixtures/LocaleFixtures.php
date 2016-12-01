<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dravencms\Model\Locale\Entities\Locale;

class LocaleFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $locale = new Locale($this->getReference('locale-currency-USD'), $this->getReference('location-country-US'), 'English', 'en_US', '.', ',', 'Y-m-d', 'H:i:s', true);
        $manager->persist($locale);
        $locale = new Locale($this->getReference('locale-currency-CZK'), $this->getReference('location-country-CZ'), 'Čeština', 'cs_CZ', ',', ' ', 'd.m.Y', 'H:i:s');
        $manager->persist($locale);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getDependencies()
    {
        return ['Dravencms\Model\Locale\Fixtures\CurrencyFixtures', 'Dravencms\Model\Location\Fixtures\CountryFixtures'];
    }
}