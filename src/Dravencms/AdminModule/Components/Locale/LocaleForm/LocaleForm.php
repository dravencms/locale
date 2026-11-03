<?php declare(strict_types = 1);
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

use Dravencms\Components\BaseForm\Form;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Location\Repository\CountryRepository;
use Dravencms\Database\EntityManager;
use Nette\Application\UI\Control;
use Nette\Security\User;


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

    /** @var User */
    private $user;

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
        User $user,
        ?Locale $locale = null
    ) {

        $this->locale = $locale;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->countryRepository = $countryRepository;
        $this->user = $user;

    }

    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('locale.admin.validation.nameRequired');

        $form->addText('code')
            ->setRequired('locale.admin.validation.localeCodeRequired');


        $charList = [
            ' ' => 'locale.admin.field.space',
            ',' => ',',
            '.' => '.',
            '\'' => '\'',
            '`' => '`'
        ];

        $form->addSelect('decPoint', null, $charList)
            ->setRequired('locale.admin.validation.decimalPointRequired');

        $form->addSelect('thousandsSep', null, $charList)
            ->setRequired('locale.admin.validation.thousandsSeparatorRequired');

        $form->addText('dateFormat')
            ->setRequired('locale.admin.validation.dateFormatRequired');

        $form->addText('timeFormat')
            ->setRequired('locale.admin.validation.timeFormatRequired');

        $form->addSelect('country', null, $this->countryRepository->getPairs());

        $form->addSelect('currency', null, $this->currencyRepository->getPairs());

        $form->addCheckbox('isDefault');
        $form->addCheckbox('isActive');


        $form->addSubmit('send', 'locale.admin.action.save');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        if ($this->locale) {
            $form->setDefaults([
                'name' => $this->locale->getName(),
                'code' => $this->locale->getCode(),
                'decPoint' => $this->locale->getDecPoint(),
                'thousandsSep' => $this->locale->getThousandsSep(),
                'dateFormat' => $this->locale->getDateFormat(),
                'timeFormat' => $this->locale->getTimeFormat(),
                'currency' => $this->locale->getCurrency()->getId(),
                'country' => $this->locale->getCountry()->getId(),
                'isDefault' => $this->locale->isDefault(),
                'isActive' => $this->locale->isActive(),
            ]);
        }


        return $form;
    }

    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();
        if (!$this->localeRepository->isNameFree($values->name, $this->locale)) {
            $form->addError('locale.admin.validation.nameTaken');
        }

        if (!$this->localeRepository->isCodeFree($values->code, $this->locale)) {
            $form->addError('locale.admin.validation.codeTaken');
        }

        if (!$this->user->isAllowed('locale', 'edit')) {
            $form->addError('locale.admin.validation.localeForbidden');
        }

        $exploded = explode('_', $values->code);
        if (count($exploded) != 2)
        {
            $form->addError('locale.admin.validation.localeCodeInvalid');
        }
    }

    public function editFormSucceeded(Form $form): void
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

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LocaleForm.latte');
        $template->render();
    }
}