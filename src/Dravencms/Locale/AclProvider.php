<?php declare(strict_types=1);

namespace Dravencms\Locale;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('locale', 'edit', 'locale.acl.permissions.edit.label', resourceLabel: 'locale.acl.resource');
        yield new AclPermission('locale', 'delete', 'locale.acl.permissions.delete.label', resourceLabel: 'locale.acl.resource');
        yield new AclPermission('locale', 'currencyEdit', 'locale.acl.permissions.currencyEdit.label', resourceLabel: 'locale.acl.resource');
        yield new AclPermission('locale', 'currencyDelete', 'locale.acl.permissions.currencyDelete.label', resourceLabel: 'locale.acl.resource');
    }
}
