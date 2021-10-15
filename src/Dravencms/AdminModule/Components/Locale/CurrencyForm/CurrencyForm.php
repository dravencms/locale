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

namespace Dravencms\AdminModule\Components\Locale\CurrencyForm;

use Dravencms\Components\BaseForm\BaseForm;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Components\BaseForm\Form;
use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Database\EntityManager;
use Nette\Application\UI\Control;
use Nette\Security\User;

/**
 * Description of CurrencyForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class CurrencyForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var User */
    private $user;

    /** @var Currency */
    private $currency = null;

    /** @var array */
    public $onSuccess = [];


    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        CurrencyRepository $currencyRepository,
        User $user,
        Currency $currency = null
    ) {

        $this->currency = $currency;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->currencyRepository = $currencyRepository;
        $this->user = $user;


        if ($this->currency) {
            $this['form']->setDefaults(
                [
                    'name' => $this->currency->getName(),
                    'code' => $this->currency->getCode(),
                    'sign' => $this->currency->getSign(),
                    'isDefault' => $this->currency->isDefault(),
                    'isActive' => $this->currency->isActive()
                ]
            );
        }
    }

    /**
     * @return BaseForm
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('Prosím zadejte jméno.');

        $form->addText('code')
            ->setRequired('Please enter locale code.');

        $form->addText('sign')
            ->setRequired('Please enter short name.');

        $form->addCheckbox('isDefault');
        $form->addCheckbox('isActive');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();
        if (!$this->currencyRepository->isNameFree($values->name, $this->currency)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->currencyRepository->isCodeFree($values->code, $this->currency)) {
            $form->addError('Tento kod je již zabrán.');
        }

        if (!$this->currencyRepository->isSignFree($values->sign, $this->currency)) {
            $form->addError('Tento kod je již zabrán.');
        }

        if (!$this->user->isAllowed('locale', 'currencyEdit')) {
            $form->addError('Nemáte oprávění editovat currency.');
        }
    }

    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();


        if ($this->currency) {
            $currency = $this->currency;
            $currency->setName($values->name);
            $currency->setCode($values->code);
            $currency->setSign($values->sign);
            $currency->setIsDefault($values->isDefault);
            $currency->setIsActive($values->isActive);
        } else {
            $currency = new Currency($values->name, $values->code, $values->sign, $values->isDefault, $values->isActive);
        }


        $this->entityManager->persist($currency);

        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CurrencyForm.latte');
        $template->render();
    }
}