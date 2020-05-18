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

class ModelLocalisationGeoZone
{
    public function getGeoZones()
    {
        return array(
            0 =>
                array(
                    'geo_zone_id'   => '5',
                    'name'          => 'NL VAT ZONE',
                    'description'   => 'VAT Zone for the Netherlands',
                    'date_added'    => '2019-04-01 12:55:02',
                    'date_modified' => '0000-00-00 00:00:00',
                ),
            1 =>
                array(
                    'geo_zone_id'   => '4',
                    'name'          => 'UK Shipping',
                    'description'   => 'UK Shipping Zones',
                    'date_added'    => '2009-06-23 01:14:53',
                    'date_modified' => '2010-12-15 15:18:13',
                ),
            2 =>
                array(
                    'geo_zone_id'   => '3',
                    'name'          => 'UK VAT Zone',
                    'description'   => 'UK VAT',
                    'date_added'    => '2009-01-06 23:26:25',
                    'date_modified' => '2010-02-26 22:33:24',
                ),
        );
    }
}