<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;

use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Database\EntityManager;
use Nette;

class LocaleRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Locale|string */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var array */
    private $localeRuntimeCache = [];

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
    public function getDefault(): ?Locale
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
    public function getOneByLanguageCode(string $languageCode): ?Locale
    {
        return $this->localeRepository->findOneBy(['languageCode' => $languageCode]);
    }
    
    /**
     * @param $languageCode string
     * @return mixed|null|Locale
     */
    public function getOneActiveByLanguageCode(string $languageCode): ?Locale
    {
        return $this->localeRepository->findOneBy(['languageCode' => $languageCode, 'isActive' => true]);
    }

    /**
     * @return array
     */
    public function getPairs(): array
    {
        return $this->localeRepository->findPairs('name');
    }

    /**
     * @param $id
     * @return null|Locale
     */
    public function getOneById(int $id): ?Locale
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
     * @param string $name
     * @param Locale|null $ignoreLocale
     * @return bool
     */
    public function isNameFree(string $name, Locale $ignoreLocale = null): bool
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
     * @param string $code
     * @param Locale|null $ignoreLocale
     * @return bool
     */
    public function isCodeFree(string $code, Locale $ignoreLocale = null): bool
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

    /**
     * @param $languageCode
     * @return Locale|null
     */
    public function getLocaleCache(string $languageCode): ?Locale
    {
        if (array_key_exists($languageCode, $this->localeRuntimeCache))
        {
            return $this->localeRuntimeCache[$languageCode];
        }
        else
        {
            $found = $this->getOneByLanguageCode($languageCode);
            $this->localeRuntimeCache[$languageCode] = $found;
            return $found;
        }
    }
}
