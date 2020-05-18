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

use DpdConnect\Label\Entity\Batch;

class ModelExtensionShippingDpdBatch extends model
{

    public function updateBatch(Batch $batch)
    {
        $assignments = [];
        foreach ($batch->getDataForUpdate() as $col => $value) {
            $assignments[] = "`$col` = " . $this->quoteOrNull($value);
        }
        $sql = "
          UPDATE `" . DB_PREFIX . "dpd_batch`
          SET ". implode(', ', $assignments). "
          WHERE `".DB_PREFIX. "dpd_batch`.`id` = ". (int) $batch->getId();

        $this->db->query($sql);
    }

    public function findBatchByNonce($nonce)
    {
        $sql = "SELECT *
            FROM `" . DB_PREFIX . "dpd_batch`
            WHERE `nonce` = ". $this->quoteOrNull($nonce);

        $query = $this->db->query($sql);

        if (empty($query->row)) {
            return null;
        }
        return Batch::fromRow($query->row);
    }

    public function startTransaction()
    {
        $this->db->query('START TRANSACTION');
    }

    public function commit()
    {
        $this->db->query('COMMIT');
    }

    public function rollback()
    {
        $this->db->query('ROLLBACK');
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
        return "'". $this->db->escape($value). "'";
    }
}
