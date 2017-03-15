<?php
/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Locale\LocaleForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Location\Repository\CountryRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of LocaleForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class LocaleForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var CountryRepository */
    private $countryRepository;

    /** @var Locale */
    private $locale;

    /** @var array */
    public $onSuccess = [];


    /**
     * LocaleForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param LocaleRepository $localeRepository
     * @param CurrencyRepository $currencyRepository
     * @param CountryRepository $countryRepository
     * @param Locale|null $locale
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        LocaleRepository $localeRepository,
        CurrencyRepository $currencyRepository,
        CountryRepository $countryRepository,
        Locale $locale = null
    ) {
        parent::__construct();

        $this->locale = $locale;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->countryRepository = $countryRepository;


        if ($this->locale) {
            $this['form']->setDefaults(
                [
                    'name' => $this->locale->getName(),
                    'code' => $this->locale->getCode(),
                    'decPoint' => $this->locale->getDecPoint(),
                    'thousandsSep' => $this->locale->getThousandsSep(),
                    'dateFormat' => $this->locale->getDateFormat(),
                    'timeFormat' => $this->locale->getTimeFormat(),
                    'currency' => $this->locale->getCurrency()->getId(),
                    'country' => $this->locale->getCountry()->getId(),
                    'isDefault' => $this->locale->isDefault(),
                    'isActive' => $this->locale->isActive()
                ]
            );
        }
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('Prosím zadejte jméno.');

        $form->addText('code')
            ->setRequired('Please enter locale code.');


        $charList = [
            ' ' => '[space]',
            ',' => ',',
            '.' => '.',
            '\'' => '\'',
            '`' => '`'
        ];

        $form->addSelect('decPoint', null, $charList)
            ->setRequired('Please enter locale decimal point.');

        $form->addSelect('thousandsSep', null, $charList)
            ->setRequired('Please enter locale thousands separator.');

        $form->addText('dateFormat')
            ->setRequired('Please enter locale date format.');

        $form->addText('timeFormat')
            ->setRequired('Please enter locale time format.');

        $form->addSelect('country', null, $this->countryRepository->getPairs());

        $form->addSelect('currency', null, $this->currencyRepository->getPairs());

        $form->addCheckbox('isDefault');
        $form->addCheckbox('isActive');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        if (!$this->localeRepository->isNameFree($values->name, $this->locale)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->localeRepository->isCodeFree($values->code, $this->locale)) {
            $form->addError('Tento kod je již zabrán.');
        }

        if (!$this->presenter->isAllowed('locale', 'edit')) {
            $form->addError('Nemáte oprávění editovat locale.');
        }

        $exploded = explode('_', $values->code);
        if (count($exploded) != 2)
        {
            $form->addError('Code has wrong format, it must be in [language_code[_country_code]] eg.: (en_US)');
        }
    }

    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $currency = $this->currencyRepository->getOneById($values->currency);
        $country = $this->countryRepository->getOneById($values->country);


        if ($this->locale) {
            $locale = $this->locale;
            $locale->setName($values->name);
            $locale->setCurrency($currency);
            $locale->setCountry($country);
            $locale->setCode($values->code);
            $locale->setDecPoint($values->decPoint);
            $locale->setThousandsSep($values->thousandsSep);
            $locale->setDateFormat($values->dateFormat);
            $locale->setTimeFormat($values->timeFormat);
            $locale->setIsDefault($values->isDefault);
            $locale->setIsActive($values->isActive);
        } else {
            $locale = new Locale($currency, $country, $values->name, $values->code, $values->decPoint, $values->thousandsSep, $values->dateFormat, $values->timeFormat, $values->isDefault, $values->isActive);
        }


        $this->localeRepository->checkDefaultLocale($locale);
        
        $this->entityManager->persist($locale);

        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LocaleForm.latte');
        $template->render();
    }
}