<?php
/**
 * This file is part of the OpenCart Shipping module of DPD Nederland B.V.
 *
 * Copyright (C) 2019 DPD Nederland B.V.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class Url
{
    private $dictionary = [
        'common/dashboarduser_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj&' => 'http://opencart.test:8888/admin/index.php?route=common/dashboard&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
        'marketplace/extensionuser_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj&type=module' => 'http://opencart.test:8888/admin/index.php?route=marketplace/extension&amp;type=module&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
        'extension/shipping/dpdbeneluxuser_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj&' => 'http://opencart.test:8888/admin/index.php?route=extension/shipping/dpdbenelux&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
        'marketplace/extensionuser_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj&type=module' => 'http://opencart.test:8888/admin/index.php?route=marketplace/extension&amp;type=module&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
        ];

    public function link($route, $data, $secure)
    {
        return $this->dictionary[$route. $data];
    }
}