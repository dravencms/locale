<?php declare(strict_types=1);

namespace Dravencms\Locale\Setup;

use Composer\InstalledVersions;
use Doctrine\ORM\EntityManagerInterface;
use Dravencms\Location\Setup\DefaultDataInstaller as LocationInstaller;
use Dravencms\Model\Locale\Entities\{Currency, Locale};
use Dravencms\Model\Location\Entities\Country;

final class DefaultDataInstaller
{
    public function __construct(private EntityManagerInterface $manager, private LocationInstaller $locations) {}

    public function install(): void
    {
        $this->manager->wrapInTransaction(function (): void {
            $this->locations->install();
            $names = require InstalledVersions::getInstallPath('umpirsky/currency-list') . '/data/en_US/currency.php';
            $currencies = $this->manager->getRepository(Currency::class);
            $hasDefault = $currencies->findOneBy(['isDefault' => true]) !== null;
            foreach (['CZK' => 'Kč', 'USD' => '$', 'EUR' => '€'] as $code => $sign) {
                if ($currencies->findOneBy(['code' => $code]) === null) {
                    $this->manager->persist(new Currency($names[$code], $code, $sign, !$hasDefault && $code === 'CZK'));
                }
            }
            $this->manager->flush();
            $locales = $this->manager->getRepository(Locale::class);
            $hasDefault = $locales->findOneBy(['isDefault' => true]) !== null;
            foreach ([
                ['en_US', 'English', 'USD', 'US', '.', ',', 'Y-m-d'],
                ['cs_CZ', 'Čeština', 'CZK', 'CZ', ',', ' ', 'd.m.Y'],
            ] as [$code, $name, $currency, $country, $decimal, $thousands, $date]) {
                if ($locales->findOneBy(['code' => $code]) !== null || $locales->findOneBy(['languageCode' => substr($code, 0, 2)]) !== null) { continue; }
                $this->manager->persist(new Locale(
                    $currencies->findOneBy(['code' => $currency]),
                    $this->manager->getRepository(Country::class)->findOneBy(['code' => $country]),
                    $name, $code, $decimal, $thousands, $date, 'H:i:s', !$hasDefault && $code === 'en_US',
                ));
            }
        });
    }
}
