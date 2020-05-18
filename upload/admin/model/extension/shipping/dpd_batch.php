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
    public function createBatch(Batch $batch)
    {
        $sql = sprintf(
            "INSERT INTO `%sdpd_batch` SET `shipmentCount` = %d, started = CURRENT_TIME(), status = '%s'",
            DB_PREFIX,
            (int)$batch->getShipmentCount(),
            Batch::StatusRequest
        );

        $this->db->query($sql);

        $batch->setId($this->db->getLastId());
    }

    public function updateBatch(Batch $batch)
    {
        $assignments = ['`status` = '. $this->quoteOrNull($batch->getStatus())];
        foreach ($batch->getDataForUpdate() as $col => $value) {
            $assignments[] = "`$col` = " . $this->quoteOrNull($value);
        }
        $sql = sprintf('UPDATE `%sdpd_batch` SET %s WHERE `%sdpd_batch`.`id` = %d',
            DB_PREFIX,
            implode(', ', $assignments),
            DB_PREFIX,
            (int)$batch->getId()
        );

        $this->db->query($sql);
    }

    public function findBatch($id)
    {
        $sql = sprintf('SELECT * FROM `%sdpd_batch` b WHERE `id` = %d',
            DB_PREFIX,
            (int)$id
        );
        $batches = $this->hydrate($this->db->query($sql));
        return empty($batches) ? null : current($batches);
    }

    /**
     * WARNING: Do not pass untrusted metadata, risk of SQL injection
     *
     * @param  array $filters
     * @return int number of Batches found
     */
    public function countBatches($filters)
    {
        $sql = sprintf('SELECT count(*) FROM `%sdpd_batch` b',
            DB_PREFIX
        );

        if (isset($filters['order_id'])) {
            $sql .= sprintf('LEFT JOIN `%sdpd_shipment` s ON s.batch_id = b.id',
                DB_PREFIX);
        }
        $sql .= $this->getFiltersSql($filters);

        $result = $this->db->query($sql);
        return $result->row['count(*)'];
    }

    /**
     * WARNING: Do not pass untrusted metadata, risk of SQL injection
     *
     * @param array $filters
     * @param string $sort
     * @param string $order
     * @param int $limit
     * @param int $start
     * @return array of Batch
     */
    public function findBatches(array $filters=[], $sort='b.id', $order='DESC', $limit=10, $start=0)
    {
        $sql = 'SELECT b.*';
        if ($sort === 'min_order_id') {
            $sql .= ', min(s.order_id) as min_order_id';
        }
        $sql .= sprintf(' FROM `%sdpd_batch` b', DB_PREFIX);
        if ($sort === 'min_order_id' || isset($filters['order_id'])) {
            $sql .= sprintf(' LEFT JOIN `%sdpd_shipment` s ON s.batch_id = b.id', DB_PREFIX);
        }
        $sql .= $this->getFiltersSql($filters);
        if ($sort === 'min_order_id') {
            $sql .= ' GROUP BY b.id';
        }
        $sql .= " ORDER BY $sort $order";
        if ($sort !== 'b.id') {
            $sql .= ", b.id $order";
        }
        $sql .= " LIMIT $start, $limit";
        return $this->hydrate($this->db->query($sql));
    }

    private function getFiltersSql($filters)
    {
        $sql = '';
        $sep = ' WHERE ';
        foreach ($filters as $filter => $value) {
            if ($filter == 'started') {
                $sql .= $sep . "b.`started` BETWEEN '". $this->db->escape($value). " 00:00:00'
                    AND '". $this->db->escape($value). " 23:59:59'";
            } elseif ($filter == 'order_id') {
                $sql .= $sep. "s.$filter = ". $this->quoteOrNull($value);
            } else {
                $sql .= $sep. "b.$filter = ". $this->quoteOrNull($value);
            }
            $sep = ' AND ';
        }
        return $sql;
    }

    /**
     * @param  $result query result
     * @return array of Batch
     */
    private function hydrate($result)
    {
        $batches = [];
        foreach ($result->rows as $row) {
                $batches[] = Batch::fromRow($row);
        }
        return $batches;
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
     * @param  string|null $value
     * @return SQL|string
     */
    private function quoteOrNull($value=null)
    {
        if (null === $value) {
            return 'NULL';
        }
        return "'{$this->db->escape($value)}'";
    }
}
