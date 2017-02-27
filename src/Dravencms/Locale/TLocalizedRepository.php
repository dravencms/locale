<?php

namespace Dravencms\Locale;

use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Dravencms\Model\Locale\Entities\ILocale;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 * @deprecated 
 */
trait TLocalizedRepository
{
    /**
     * @param Query $query
     * @param ILocale $locale
     * @return Query
     */
    public function addTranslationWalkerToQuery(Query $query, ILocale $locale)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $query->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $locale->getLanguageCode()
        );
        $query->setHydrationMode(TranslationWalker::HYDRATE_OBJECT_TRANSLATION);
        $query->setHint(Query::HINT_REFRESH, true);

        return $query;
    }

    /**
     * @param Query $query
     * @param ILocale $locale
     * @return array
     */
    public function getTranslatedArrayResult(Query $query, ILocale $locale)
    {
        $this->addTranslationWalkerToQuery($query, $locale);

        return $query->getArrayResult();
    }

    /**
     * @param Query $query
     * @param ILocale $locale
     * @param int $hydrationMode
     * @return array
     */
    public function getTranslatedResult(Query $query, ILocale $locale, $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $this->addTranslationWalkerToQuery($query, $locale);

        switch ($hydrationMode)
        {
            case Query::HYDRATE_OBJECT:
                $translatorHydrationMode = TranslationWalker::HYDRATE_OBJECT_TRANSLATION;
                break;

            case Query::HYDRATE_SIMPLEOBJECT:
                $translatorHydrationMode = TranslationWalker::HYDRATE_SIMPLE_OBJECT_TRANSLATION;
                break;

            default:
                $translatorHydrationMode = $hydrationMode;
                break;
        }

        return $query->getResult($translatorHydrationMode);
    }

    /**
     * @param $repository
     * @param ILocale $locale
     * @param array $criteria
     * @param array|null $orderBy
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslatedOneBy($repository, ILocale $locale, array $criteria, array $orderBy = null)
    {
        $qb = $repository->createQueryBuilder('e')
            ->whereCriteria($criteria)
            ->autoJoinOrderBy((array) $orderBy);

        $query = $qb->getQuery();

        $this->addTranslationWalkerToQuery($query, $locale);

        return $query->getOneOrNullResult();
    }
}