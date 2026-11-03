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

namespace Dravencms\AdminModule\Components\Locale\LocaleGrid;


use Dravencms\User\Attributes\IsAllowed;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Application\UI\Control;
use Nette\Security\User;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;


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

    /** @var User */
    private $user;

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
    public function __construct(LocaleRepository $localeRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager, User $user)
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->localeRepository = $localeRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
    }


    /**
     * @param string $name
     * @return Grid
     * @throws \Contributte\Datagrid\Exception\DatagridException
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);
        $grid->setWritePermission('locale', 'edit');

        $grid->setDataSource($this->localeRepository->getLocaleQueryBuilder());

        $grid->addColumnText('flag', 'locale.admin.field.flag')
            ->setAlign('center')
            ->setTemplate(__DIR__ . '/flag.latte');
        
        $grid->addColumnText('name', 'locale.admin.field.name')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnBoolean('isDefault', 'locale.admin.field.default');

        $grid->addColumnBoolean('isActive', 'locale.admin.field.active');

        if ($this->user->isAllowed('locale', 'edit')) {

            $grid->addAction('edit', '', 'edit')
                ->setIcon('pencil')
                ->setTitle('locale.admin.action.edit')
                ->setClass('btn btn-sm btn-primary');
        }

        if ($this->user->isAllowed('locale', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('locale.admin.action.delete')
                ->setClass('btn btn-sm btn-danger ajax')
                ->setConfirmation(new StringConfirmation('locale.admin.confirm.delete', 'name'));

            $grid->addGroupAction('locale.admin.action.delete')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        return $grid;
    }

    /**
     * @param array $ids
     * @throws \Exception
     */
    public function gridGroupActionDelete(array $ids): void
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     */
    #[\Dravencms\User\Attributes\IsAllowed('locale', 'delete')]
    #[\Dravencms\Security\Mutation]
    public function handleDelete($id): void
    {
        $this->requirePermission('locale', 'delete');
        $locales = $this->localeRepository->getById($id);
        foreach ($locales AS $locale)
        {
            $this->entityManager->remove($locale);
        }

        try {
            $this->entityManager->flush();
            $this->onDelete(true);
        } catch (ForeignKeyConstraintViolationException $exception) {
            $this->onDelete(false);
        }
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LocaleGrid.latte');
        $template->render();
    }
}
