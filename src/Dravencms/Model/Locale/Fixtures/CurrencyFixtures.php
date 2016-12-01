<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dravencms\Model\Locale\Entities\Currency;

class CurrencyFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $default = 'CZK';
        $codeToSign = [
            'CZK' => 'Kč',
            'USD' => '$',
            'EUR' => '€',
        ];
        $currencies = require_once __DIR__."/../../../../../../../../vendor/umpirsky/currency-list/data/en_US/currency.php";
        foreach ($currencies AS $code => $name)
        {
            if (array_key_exists($code, $codeToSign))
            {
                $country = new Currency($name, $code, $codeToSign[$code], ($default == $code));
                $manager->persist($country);
                $this->addReference('locale-currency-'.$code, $country);
            }
        }
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