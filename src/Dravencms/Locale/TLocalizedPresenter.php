<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 19:30
 */

namespace Dravencms\Locale;


use Kdyby\Translation\Translator;
use Tracy\Debugger;

trait TLocalizedPresenter
{
    /** @persistent */
    public $locale;

    /** @var Locale @inject */
    public $localeClass;

    /** @var Translator @inject */
    public $translator;

    public function startup()
    {
        $this->template->lang = $this->locale;

        /** @var TranslatableListener $gedmoTranslatable */
        $gedmoTranslatable = $this->context->getService('gedmo.gedmo.translatable');
        if ($gedmoTranslatable) //!FIXME Move somewhere else
        {
            $localeLocaleRepository = $this->context->getByType('Dravencms\Model\Locale\Repository\LocaleRepository', false);
            if ($localeLocaleRepository)
            {
                $gedmoTranslatable->setDefaultLocale($localeLocaleRepository->getDefault()->getLanguageCode());
                $gedmoTranslatable->setTranslationFallback(true);
                $gedmoTranslatable->setTranslatableLocale($localeLocaleRepository->getCurrentLocale()->getLanguageCode());
            }
        }

        parent::startup();
    }

}