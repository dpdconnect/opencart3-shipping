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

class ModelLocalisationTaxClass
{
    /**
     * @return array of row-array
     */
    public function getTaxClasses()
    {
        return array(
            0 =>
                array(
                    'tax_class_id'  => '9',
                    'title'         => 'Taxable Goods',
                    'description'   => 'Taxed goods',
                    'date_added'    => '2009-01-06 23:21:53',
                    'date_modified' => '2019-04-01 13:04:25',
                ),
            1 =>
                array(
                    'tax_class_id'  => '10',
                    'title'         => 'Downloadable Products',
                    'description'   => 'Downloadable',
                    'date_added'    => '2011-09-21 22:19:39',
                    'date_modified' => '2019-04-01 13:05:08',
                ),
        );
    }
}