<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;


use Dravencms\Model\Locale\Entities\Currency;
use Kdyby\Doctrine\EntityManager;
use Nette;

class CurrencyRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $currencyRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;


    /** @var Currency|mixed|null */
    private $currentCurrency;

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

        $this->currentCurrency = $this->findCurrentCurrency();
    }

    /**
     * @param $code
     * @return mixed|null|Currency
     */
    public function getByCode($code)
    {
        return $this->currencyRepository->findOneBy(['code' => $code]);
    }

    /**
     * @return array
     */
    public function getPairs()
    {
        return $this->currencyRepository->findPairs('name');
    }

    /**
     * @param $id
     * @return null|Currency
     */
    public function getOneById($id)
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
     * @param $name
     * @param Currency|null $ignoreCurrency
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Currency $ignoreCurrency = null)
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
     * @param $code
     * @param Currency|null $ignoreCurrency
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isCodeFree($code, Currency $ignoreCurrency = null)
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
     * @param $sign
     * @param Currency|null $ignoreCurrency
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isSignFree($sign, Currency $ignoreCurrency = null)
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
    public function getActive($isActive = true)
    {
        return $this->currencyRepository->findBy(['isActive' => $isActive]);
    }
}