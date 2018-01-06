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

namespace Dravencms\AdminModule\Components\Locale\CurrencyGrid;

use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * Description of CurrencyGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class CurrencyGrid extends Control
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * AclOperationGrid constructor.
     * @param CurrencyRepository $currencyRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(CurrencyRepository $currencyRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->currencyRepository = $currencyRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @param $name
     * @return Grid
     */
    public function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->currencyRepository->getCurrencyQueryBuilder());

        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('sign', 'Sign')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('code', 'Code')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isDefault', 'Default');

        $grid->addColumnBoolean('isActive', 'Active');

        if ($this->presenter->isAllowed('locale', 'currencyEdit')) {
            $grid->addAction('edit', null)
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('locale', 'currencyDelete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'name');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(locale, currencyDelete)
     */
    public function handleDelete($id)
    {
        $locales = $this->currencyRepository->getById($id);
        foreach ($locales AS $locale)
        {
            $this->entityManager->remove($locale);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CurrencyGrid.latte');
        $template->render();
    }
}
