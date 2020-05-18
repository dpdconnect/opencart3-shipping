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


class ModelExtensionShippingDpdProductExtension extends model
{
    const UpdateProps = [
        'export_hsc', 'export_value', 'export_origin_country',
    ];

    public function create(array $data)
    {
        $sql = sprintf('INSERT INTO `%sdpd_product_extension` SET `product_id` = %d,',
            DB_PREFIX,
            (int)$data['product_id']
        );
        $sql .= implode(', ', $this->getAssignments($data));

        $this->db->query($sql);
    }

    public function update(array $data)
    {
        $sql = sprintf('UPDATE `%sdpd_product_extension` SET %s WHERE `%sdpd_product_extension`.`product_id` = %d',
            DB_PREFIX,
            implode(', ', $this->getAssignments($data)),
            DB_PREFIX,
            (int)$data['product_id']);

        $this->db->query($sql);
    }

    public function save($data)
    {
        $found = $this->find($data['product_id']);
        empty($found) ? $this->create($data) : $this->update($data);

        $result = ['product_id' => $data['product_id']];
        foreach (self::UpdateProps as $col) {
            $result[$col] = $data[$col];
        }
        return $result;
    }

    public function validate($data)
    {
        $errors = [];
        if (!is_numeric($data['product_id'])) {
            throw new \LogicException("Product id not numeric: '". $data['product_id']. "'");
        }
        if (!in_array(strlen($data['export_origin_country']), [0, 2], true)) {
            $errors['export_origin_country'] = 'error_export_origin_country_length';
        }
        if (!empty($data['export_value']) && (!is_numeric($data['export_value']) || $data['export_value'] < 0)) {
            $errors['export_value'] = 'error_export_value_no_positive_number';
        }
        if (!empty($data['export_hsc']) && strlen($data['export_hsc']) > 8) {
            $errors['export_hsc'] = 'error_export_hsc_too_long';
        }

        return $errors;
    }

    private function getAssignments($data)
    {
        $assignments = [];
        foreach (self::UpdateProps as $col) {
            $assignments[] = "`$col` = " . $this->quoteOrNull($data[$col]);
        }
        return $assignments;
    }

    public function find($product_id)
    {
        $sql = sprintf('SELECT * FROM `%sdpd_product_extension` b WHERE `product_id` = %d',
            DB_PREFIX,
            (int)$product_id);

        return $this->db->query($sql)->row;
    }

    /**
     *
     * @param  string|null $value
     * @return SQL representation of $value, either NULL or literal string
     */
    private function quoteOrNull($value=null)
    {
        if (null === $value) {
            return 'NULL';
        }
        return "'{$this->db->escape($value)}'";
    }
}