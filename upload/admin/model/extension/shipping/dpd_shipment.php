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
    const StatusRequest = 'status_request';
    const StatusQueued = 'status_queued';
    const StatusProcessing = 'status_processing';
    const StatusSuccess = 'status_success';
    const StatusFailed = 'status_failed';
    const JobStateQueued = 1;
    const JobStateProcessing = 2;
    const JobStateSuccess = 4;
    const JobStateFailed = 8;

    static function getParcelnumbers($data)
    {
        $result = [];
        if (!empty($data['mps_id'])) {
            foreach (unserialize($data['label_numbers']) as $parcelData) {
                $result[] = $parcelData['parcel_number'];
            }
        }
        return $result;
    }

    public function registerShipment($data, $existing=[])
    {
        if (empty($existing)) {
            return $this->createShipment($data);
        }
        if ($data['order_id'] != $existing['order_id']) {
            throw new \Exception('Different order id');
        }
        if ($data['is_return'] != $existing['is_return']) {
            throw new \Exception('Different is_return');
        }
        $this->updateShipment($data, $existing);
    }

    public function createShipment($data)
    {

        $sql = "
        INSERT INTO `" . DB_PREFIX . "dpd_shipment` 
		SET `order_id` = '" . $this->db->escape($data['order_id']) . "',
		batch_id = ". (int) $data['batch_id']. ",  
		mps_id = '" . $this->db->escape($data['mps_id']) . "',
		label_numbers = '" . $this->db->escape($data['label_numbers']) . "',
		label = " . $this->quoteOrNull($data['label']) . ",
		is_return = '" . $this->db->escape($data['is_return']) . "',
		date_added = NOW(),
		date_modified = NOW(),
		`is_current` = ". (int) $data['is_current']. ",
		`error` = ". $this->quoteOrNull($data['error']) . ",
		`job_id` = " . $this->quoteOrNull($data['job_id']) . ",
		`job_state` = ". $this->quoteOrNull($data['job_state']);

        $this->db->query($sql);

        $dpd_shipment_id = $this->db->getLastId();
        return $dpd_shipment_id;
    }

    public function updateShipment($data)
    {
        $assignments = [];
        foreach ($data as $col => $value) {
            if ($col != 'order' && $col != 'dpd_shipment_id' && $col != 'date_modified') {
                $assignments[] = "`$col` = " . $this->quoteOrNull($value);
            }
        }
        if (empty($assignments)) {
            return;
        }
        $assignments[] = 'date_modified = NOW()';
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

    public function getShipment($order_id, $isReturn = false)
    {
        $sql = "SELECT * 
            FROM `" . DB_PREFIX . "dpd_shipment` 
            WHERE `order_id` = '" . $this->db->escape($order_id) . "' 
            AND `is_return` = " . $this->quoteOrNull($isReturn) . "
            AND `is_current` = 1";

        $query = $this->db->query($sql);
        //var_dump($query);
        return $query->row;
    }

    public function getShipmentsWithBatchId($batchId)
    {
        $sql = "SELECT * 
            FROM `" . DB_PREFIX . "dpd_shipment` 
            WHERE `batch_id` = " . (int) $batchId . "
            ORDER BY order_id ASC, is_return ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * WARNING: Do not pass untrusted metadata, risk of SQL injection
     *
     * @param  array $filters
     * @return int number of Shipments found
     */
    public function countShipments($filters)
    {
        $sql = "
          SELECT count(*)
          FROM `". DB_PREFIX . "dpd_shipment` s";

        $sql .= $this->getFiltersSql($filters);

        $result = $this->db->query($sql);
        return $result->row['count(*)'];
    }

    /**
     * WARNING: Do not pass untrusted metadata, risk of SQL injection
     *
     * @param  array  $filters
     * @param  string $sort
     * @param  string $order
     * @param  int    $limit
     * @param  int    $start
     * @return array of row array
     */
    public function findShipments(array $filters=[], $sort='s.dpd_shipment_id', $order='DESC', $limit=10, $start=0)
    {
        $sql = "SELECT s.*";
        $sql .= "
          FROM `". DB_PREFIX . "dpd_shipment` s";
        $sql .= $this->getFiltersSql($filters);
        $sql .= "
          ORDER BY $sort $order";
        $sql .= "
          LIMIT $start, $limit 
          ";
        //die($sql);
        return $this->db->query($sql)->rows;
    }

    private function getFiltersSql($filters)
    {
        $sql = '';
        $sep = ' WHERE ';
        foreach ($filters as $filter => $value) {
            if ($filter == 'created') {
                $sql .= $sep . "s.`date_added` BETWEEN '". $this->db->escape($value). " 00:00:00'
                    AND '". $this->db->escape($value). " 23:59:59'";
            } elseif ($filter == 'status') {
                $sql .= $sep. $this->getStatusCriterium($value);
            } else {
                $sql .= $sep. "s.$filter = ". $this->quoteOrNull($value);
            }
            $sep = ' AND ';
        }
        return $sql;
    }

    private function getStatusCriterium($value)
    {
        if (self::StatusSuccess == $value) {
            return "s.`mps_id` != '' AND s.`mps_id` IS NOT NULL";
        }
        if (self::StatusFailed == $value) {
            return "(s.`job_state` = ". self::JobStateFailed
              . " OR s.`error` IS NOT NULL AND s.`error` != 'null')";
        }
        if (self::StatusQueued == $value) {
            return "(s.`mps_id` = '' OR s.`mps_id` IS NULL) AND s.`job_state` = ". self::JobStateQueued;
        }
        if (self::StatusProcessing == $value) {
            return "(s.`mps_id` = '' OR s.`mps_id` IS NULL) AND s.`job_state` = ". self::JobStateProcessing;
        }
        if (self::StatusRequest == $value) {
            return "(s.`mps_id` = '' OR s.`mps_id` IS NULL) AND s.`job_state` IS NULL AND (s.`error` IS NULL OR s.`error` = 'null')";
        }
        throw new \LogicException('Unknown status: '. $value);
    }

    public function getParcelshop($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dpd_order_parcelshop` WHERE `order_id` = '" . $this->db->escape($order_id) . "'");

        return $query->row;
    }

    public function getStatus($data)
    {
        if (!empty($data['mps_id'])) {
            return self::StatusSuccess;
        }
        // If processed synchonously status remains Request until response is processed
        if (!empty($data['error']) && $data['error'] != 'null') {
            return self::StatusFailed;
        }
        switch ($data['job_state']) {
        case self::JobStateQueued: 
            return self::StatusQueued;
        case self::JobStateProcessing: 
            return self::StatusProcessing;
        case self::JobStateSuccess: 
            return self::StatusFailed; // Job finished but but no mps_id!
        case self::JobStateFailed: 
            return self::StatusFailed;
        }
        // If processed synchonously status remains Request until response is processed
        return self::StatusRequest;
    }

    public function getErrorText($data)
    {
        $error = json_decode($data['error']);

        if (empty($error->_embedded->errors)) {
            if (empty($error->message)) {
                return null;
            }
            return $error->message;
        }

        $arr = [];
        foreach ($error->_embedded->errors as $embedded) {
            $text = '';
            if (isset($embedded->metaDataPath)) {
                $text = $embedded->metaDataPath. ': ';
            }
            if (isset($embedded->dataPath)) {
                $text = $embedded->dataPath. ': ';
            }
            $text .= $embedded->message;
            $arr[] = $text;
        }

        return implode("; \n", $arr);
    }

    public function install()
    {
        $this->createTables();
        if (!$this->isShipmentTableVersion2()) {
            $this->upgradeTables();
        }
        $this->createIndexes();

    }

    private function isShipmentTableVersion2()
    {
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "dpd_shipment`");
        foreach ($result->rows as $row) {
            if ($row['Field'] == 'job_id') {
                return true;
            }
        }
        return false;
    }

    private function createTables()
    {
        $this->db->query(
            "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dpd_shipment` (
			  `dpd_shipment_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `batch_id` int NULL DEFAULT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `mps_id` VARCHAR(255) DEFAULT NULL,
			  `label_numbers` TEXT NOT NULL,
			  `label` MEDIUMBLOB DEFAULT NULL,
			  `is_return` tinyint(1) NOT NULL,
			  `error` TEXT NULL DEFAULT NULL,
			  `is_current` tinyint NOT NULL DEFAULT 0,
              `job_id` varchar(255) DEFAULT NULL,
              `job_state` tinyint(4) DEFAULT NULL,
			  PRIMARY KEY (`dpd_shipment_id`)
			) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci
		"
        );

        $this->db->query(
            "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dpd_order_parcelshop` (
			  `dpd_order_parcelshop_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `parcelshop_id` varchar(255) DEFAULT NULL,
			  `parcelshop_company` VARCHAR(255) NOT NULL,
			  `parcelshop_street` VARCHAR(255) NOT NULL,
			  `parcelshop_zipcode` VARCHAR(255) NOT NULL,
			  `parcelshop_city` VARCHAR(255) NOT NULL,
			  `parcelshop_country` VARCHAR(255) NOT NULL,
			  PRIMARY KEY (`dpd_order_parcelshop_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci
		"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dpd_batch` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `started` timestamp NOT NULL DEFAULT current_timestamp(),
              `nonce` varchar(255) DEFAULT NULL,
              `shipmentCount` smallint(5) UNSIGNED NOT NULL,
              `successCount` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
              `failureCount` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
              `status` VARCHAR(23) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        "
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dpd_product_extension` ( 
                `product_id` INT NOT NULL , 
                `export_hsc` VARCHAR(255) NULL , 
                `export_value` DECIMAL(15,4) NULL , 
                `export_origin_country` VARCHAR(2) NULL,
                PRIMARY KEY (`product_id`) 
            ) ENGINE = InnoDB DEFAULT COLLATE=utf8_general_ci
        "
        );
    }

    private function upgradeTables()
    {
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` CHANGE `label` `label` MEDIUMBLOB NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ADD `error` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ADD `batch_id` int NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ADD `is_current` tinyint NOT NULL DEFAULT 1");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ADD `job_id` VARCHAR(255) NULL");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ADD `job_state` tinyint(4) DEFAULT NULL");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_shipment` ENGINE = InnoDB");
    }

    private function createIndexes()
    {
        $this->db->query(
            "
            ALTER TABLE `" . DB_PREFIX . "dpd_batch` 
            ADD UNIQUE (`nonce`)"
        );

        $this->db->query(
            "
            ALTER TABLE `" . DB_PREFIX . "dpd_shipment` 
            ADD UNIQUE (`batch_id`, `order_id`, `is_return`)"
        );

        $this->db->query(
            "
            ALTER TABLE `" . DB_PREFIX . "dpd_shipment` 
            ADD INDEX (`order_id`, `is_return`, `is_current`)"
        );

        $this->db->query("ALTER TABLE `" . DB_PREFIX . "dpd_order_parcelshop` ADD INDEX(`order_id`)");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dpd_shipment`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dpd_order_parcelshop`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dpd_batch`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dpd_product_extension`");
    }
}