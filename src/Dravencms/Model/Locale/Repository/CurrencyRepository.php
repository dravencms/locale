<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;


use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Database\EntityManager;

class CurrencyRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Currency|string */
    private $currencyRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * CurrencyRepository constructor.
     * @param EntityManager $entityManager
     * @param LocaleRepository $localeRepository
     */
    public function __construct(EntityManager $entityManager, LocaleRepository $localeRepository)
    {
        $this->entityManager = $entityManager;
        $this->currencyRepository = $entityManager->getRepository(Currency::class);
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param $code
     * @return mixed|null|Currency
     */
    public function getByCode(string $code): ?Currency
    {
        return $this->currencyRepository->findOneBy(['code' => $code]);
    }

    /**
     * @return array
     */
    public function getPairs(): array
    {
        return $this->currencyRepository->findPairs('name');
    }

    /**
     * @param $id
     * @return null|Currency
     */
    public function getOneById(int $id): ?Currency
    {
        return $this->currencyRepository->find($id);
    }

    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        return $this->currencyRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getCurrencyQueryBuilder()
    {
        $qb = $this->currencyRepository->createQueryBuilder('c')
            ->select('c');
        return $qb;
    }

    /**
     * @param string $name
     * @param Currency|null $ignoreCurrency
     * @return bool
     */
    public function isNameFree(string $name, Currency $ignoreCurrency = null): bool
    {
        $qb = $this->currencyRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($ignoreCurrency)
        {
            $qb->andWhere('c != :ignoreCurrency')
                ->setParameter('ignoreCurrency', $ignoreCurrency);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param string $code
     * @param Currency|null $ignoreCurrency
     * @return bool
     */
    public function isCodeFree(string $code, Currency $ignoreCurrency = null): bool
    {
        $qb = $this->currencyRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.code = :code')
            ->setParameters([
                'code' => $code
            ]);

        if ($ignoreCurrency)
        {
            $qb->andWhere('c != :ignoreCurrency')
                ->setParameter('ignoreCurrency', $ignoreCurrency);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param string $sign
     * @param Currency|null $ignoreCurrency
     * @return bool
     */
    public function isSignFree(string $sign, Currency $ignoreCurrency = null): bool
    {
        $qb = $this->currencyRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.sign = :sign')
            ->setParameters([
                'sign' => $sign
            ]);

        if ($ignoreCurrency)
        {
            $qb->andWhere('c != :ignoreCurrency')
                ->setParameter('ignoreCurrency', $ignoreCurrency);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param bool $isActive
     * @return array
     */
    public function getActive(bool $isActive = true)
    {
        return $this->currencyRepository->findBy(['isActive' => $isActive]);
    }
}