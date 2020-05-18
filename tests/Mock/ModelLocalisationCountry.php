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

class ModelLocalisationCountry
{
    public function getCountries()
    {
        return array(
            array('country_id' => '21','name' => 'Belgium','iso_code_2' => 'BE','iso_code_3' => 'BEL','address_format' => '{firstname} {lastname}
{company}
{address_1}
{address_2}
{postcode} {city}
{country}','postcode_required' => '0','status' => '1'),
            array('country_id' => '124','name' => 'Luxembourg','iso_code_2' => 'LU','iso_code_3' => 'LUX','address_format' => '','postcode_required' => '0','status' => '1'),
            array('country_id' => '150','name' => 'Netherlands','iso_code_2' => 'NL','iso_code_3' => 'NLD','address_format' => '','postcode_required' => '0','status' => '1')
        );
    }
}