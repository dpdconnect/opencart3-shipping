<?php
/**
 * This file is part of the OpenCart Shipping module of DPD Nederland B.V.
 *
 * Copyright (C) 2018  DPD Nederland B.V.
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

class ModelExtensionShippingDpdShipment extends Model
{


    public function updateShipment($data)
    {
        $assignments = [];
        foreach ($data as $col => $value) {
            if ($col != 'order' && $col != 'dpd_shipment_id') {
                $assignments[] = "`$col` = " . $this->quoteOrNull($value);
            }
        }
        if (empty($assignments)) {
            return;
        }
        $sql = "
          UPDATE `" . DB_PREFIX . "dpd_shipment` 
          SET ". implode(', ', $assignments). "
          WHERE `".DB_PREFIX. "dpd_shipment`.`dpd_shipment_id` = ". $this->quoteOrNull($data['dpd_shipment_id']);

        $this->db->query($sql);
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
        if (false === $value) {
            return 0;
        }
        return "'". $this->db->escape($value). "'";
    }

    public function selectWithBatchIdOrderIdIsReturn($batch_id, $order_id, $isReturn)
    {
        $sql = "SELECT * 
            FROM `" . DB_PREFIX . "dpd_shipment` 
            WHERE `order_id` = '" . $this->db->escape($order_id) . "' 
            AND `is_return` = '" . $this->db->escape($isReturn) . "'
            AND `batch_id` = ". (int) $batch_id;

        $query = $this->db->query($sql);

        return $query->row;
    }

}