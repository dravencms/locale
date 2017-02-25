<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;

use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Model\Locale\Entities\Locale;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette;

class LocaleRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * LocaleRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->localeRepository = $entityManager->getRepository(Locale::class);
    }

    /**
     * @return mixed|null|Locale
     */
    public function getDefault()
    {
        return $this->localeRepository->findOneBy(['isDefault' => true]);
    }

    /**
     * @return Locale[]
     */
    public function getActive()
    {
        return $this->localeRepository->findBy(['isActive' => true], ['isDefault' => 'DESC']);
    }

    /**
     * @param $languageCode
     * @return mixed|null|Locale
     */
    public function getOneByLanguageCode($languageCode)
    {
        return $this->localeRepository->findOneBy(['languageCode' => $languageCode]);
    }

    /**
     * @return array
     */
    public function getPairs()
    {
        return $this->localeRepository->findPairs('name');
    }

    /**
     * @param $id
     * @return null|Locale
     */
    public function getOneById($id)
    {
        return $this->localeRepository->find($id);
    }

    /**
     * @param $id
     * @return Locale[]
     */
    public function getById($id)
    {
        return $this->localeRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getLocaleQueryBuilder()
    {
        $qb = $this->localeRepository->createQueryBuilder('l')
            ->select('l');
        return $qb;
    }

    /**
     * @param $name
     * @param Locale|null $ignoreLocale
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Locale $ignoreLocale = null)
    {
        $qb = $this->localeRepository->createQueryBuilder('l')
            ->select('l')
            ->where('l.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($ignoreLocale)
        {
            $qb->andWhere('l != :ignoreLocale')
                ->setParameter('ignoreLocale', $ignoreLocale);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param $code
     * @param Locale|null $ignoreLocale
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isCodeFree($code, Locale $ignoreLocale = null)
    {
        $qb = $this->localeRepository->createQueryBuilder('l')
            ->select('l')
            ->where('l.code = :code')
            ->setParameters([
                'code' => $code
            ]);

        if ($ignoreLocale)
        {
            $qb->andWhere('l != :ignoreLocale')
                ->setParameter('ignoreLocale', $ignoreLocale);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param Locale $locale
     */
    public function checkDefaultLocale(Locale $locale)
    {
        if ($locale->isDefault())
        {
            $qb = $this->localeRepository->createQueryBuilder('l');
            $qb->update()
                ->set('l.isDefault', $qb->expr()->literal(false))
                ->where('l != :defaultLocale')
                ->setParameter('defaultLocale', $locale)
                ->getQuery()
                ->execute();
        }
    }
}