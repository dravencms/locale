<?php declare(strict_types = 1);

namespace Dravencms\Locale;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Contributte\Translation\Translator;
use Nette\SmartObject;


/**
 * Class CurrentLocaleResolver
 * @package Dravencms\Locale
 */
class CurrentLocaleResolver
{
    use SmartObject;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Translator */
    private $translator;

    /**
     * @var null|Locale
     */
    private $currentLocale = null;

    /**
     * CurrentLocale constructor.
     * @param LocaleRepository $localeRepository
     * @param Translator $translator
     */
    public function __construct(
        LocaleRepository $localeRepository,
        Translator $translator
    )
    {
        $this->localeRepository = $localeRepository;
        $this->translator = $translator;
    }


    /**
     * @return Locale|mixed|null
     * @throws \Exception
     */
    private function findCurrentLocale(): Locale
    {
        // Set current locale model
        if ($found = $this->localeRepository->getOneActiveByLanguageCode($this->translator->getLocale())) {
            return $found;
        } else {
            //Not found
            if ($found = $this->localeRepository->getDefault()) {
                return $found;
            } else {
                throw new \Exception('No default locale selected');
            }
        }
    }

    /**
     * @return Locale|null
     * @throws \Exception
     */
    public function getCurrentLocale(): Locale
    {
        if (is_null($this->currentLocale))
        {
            $this->currentLocale = $this->findCurrentLocale();
        }

        return $this->currentLocale;
    }
}
