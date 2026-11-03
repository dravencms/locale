<?php declare(strict_types=1);

namespace Dravencms\Locale;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('locale', 'locale.menu.root', icon: 'fa-language', order: 900);
        yield new AdminMenuItem('locale.locales', 'locale.menu.locales', destination: ':Admin:Locale:Locale:default', parent: 'locale', icon: 'fa-language', order: 10, resource: 'locale', privilege: 'edit');
        yield new AdminMenuItem('locale.currencies', 'locale.menu.currencies', destination: ':Admin:Locale:Currency:default', parent: 'locale', icon: 'fa-usd', order: 20, resource: 'locale', privilege: 'currencyEdit');
    }
}
