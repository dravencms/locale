<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Entities\Locale;

class LocaleFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $locale = new Locale($this->getReference('locale-currency-USD', Currency::class), $this->getReference('location-country-US', \Dravencms\Model\Location\Entities\Country::class), 'English', 'en_US', '.', ',', 'Y-m-d', 'H:i:s', true);
        $manager->persist($locale);
        $locale = new Locale($this->getReference('locale-currency-CZK', Currency::class), $this->getReference('location-country-CZ', \Dravencms\Model\Location\Entities\Country::class), 'Čeština', 'cs_CZ', ',', ' ', 'd.m.Y', 'H:i:s');
        $manager->persist($locale);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return ['Dravencms\Model\Locale\Fixtures\CurrencyFixtures', 'Dravencms\Model\Location\Fixtures\CountryFixtures'];
    }
}
