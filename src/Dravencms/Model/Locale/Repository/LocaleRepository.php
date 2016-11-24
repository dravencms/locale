<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;

use Dravencms\Model\Locale\Entities\Locale;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\ILocaleRepository;

class LocaleRepository implements ICmsComponentRepository, ILocaleRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Translator */
    private $translator;

    /**
     * @var Nette\Security\User
     */
    private $user;


    private $currentLocale;

    /**
     * LocaleRepository constructor.
     * @param EntityManager $entityManager
     * @param Translator $translator
     * @param Nette\Security\User $user
     */
    public function __construct(EntityManager $entityManager, Translator $translator, Nette\Security\User $user)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->user = $user;
        $this->localeRepository = $entityManager->getRepository(Locale::class);

        $this->currentLocale = $this->findCurrentLocale();
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
    public function getByLanguageCode($languageCode)
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
     * @return Locale|mixed|null
     * @throws \Exception
     */
    private function findCurrentLocale()
    {
        $user = $this->user->getIdentity();
        if ($user)
        {
            $userLocale = $user->getLocale();
            if ($userLocale)
            {
                $this->translator->setLocale($userLocale->getLanguageCode());
            }
        }

        // Set current locale model
        if ($found = $this->getByLanguageCode($this->translator->getLocale())) {
            return $found;
        } else {
            //Not found
            if ($found = $this->getDefault()) {
                return $found;
            } else {
                throw new \Exception('No default locale selected');
            }
        }
    }

    /**
     * @return Locale|mixed|null
     * @throws \Exception
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLocalizedDateFormat()
    {
        return $this->currentLocale->getDateFormat();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLocalizedTimeFormat()
    {
        return $this->currentLocale->getTimeFormat();
    }

    /**
     * @return string
     */
    public function getLocalizedDateTimeFormat()
    {
        return $this->getLocalizedDateFormat().' '.$this->getLocalizedTimeFormat();
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
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return void
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        // TODO: Implement getActionOption() method.
    }

    public function getActionOptions($componentAction)
    {
        // TODO: Implement getActionOptions() method.
    }
}