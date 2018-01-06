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

namespace Dravencms\AdminModule\Components\Locale\LocaleGrid;

use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

/**
 * Description of LocaleGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class LocaleGrid extends Control
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * AclOperationGrid constructor.
     * @param LocaleRepository $localeRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(LocaleRepository $localeRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->localeRepository = $localeRepository;
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

        $grid->setDataSource($this->localeRepository->getLocaleQueryBuilder());

        $grid->addColumnText('flag', 'Flag')
            ->setAlign('center')
            ->setTemplate(__DIR__ . '/flag.latte');
        
        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnBoolean('isDefault', 'Default');

        $grid->addColumnBoolean('isActive', 'Active');

        if ($this->presenter->isAllowed('locale', 'edit')) {

            $grid->addAction('edit', '', 'edit')
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('locale', 'delete')) {
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
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids)
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(locale, delete)
     */
    public function handleDelete($id)
    {
        $locales = $this->localeRepository->getById($id);
        foreach ($locales AS $locale)
        {
            $this->entityManager->remove($locale);
        }

        $this->entityManager->flush();

        if ($this->presenter->isAjax()) {
            $this['grid']->reload();
        } else {
            $this->onDelete();
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LocaleGrid.latte');
        $template->render();
    }
}
