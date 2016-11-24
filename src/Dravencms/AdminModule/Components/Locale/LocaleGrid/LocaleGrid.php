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
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

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
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->localeRepository->getLocaleQueryBuilder());

        $grid->addColumnText('flag', 'Flag')
            ->setCustomRender(function ($row) {
                $el = Html::el('span');
                $el->class = sprintf('flag-icon flag-icon-%s', strtolower($row->getCountry()->getCode()));
                return $el;
            });

        $grid->getColumn('flag')->cellPrototype->class[] = 'center';

        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();


        $grid->addColumnBoolean('isDefault', 'Default');

        $grid->addColumnBoolean('isActive', 'Active');


        if ($this->presenter->isAllowed('locale', 'edit')) {
            $grid->addActionHref('edit', 'Upravit')
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('locale', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat město %s ?', $row->name];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i locales ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $locales = $this->localeRepository->getById($id);
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
        $template->setFile(__DIR__ . '/LocaleGrid.latte');
        $template->render();
    }
}
