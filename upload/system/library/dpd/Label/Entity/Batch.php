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

namespace DpdConnect\Label\Entity;


class Batch
{
    const UpdateProps = [
        'started', 'nonce', 'shipmentCount', 'successCount', 'failureCount',
    ];
    const StatusRequest = 'status_request';
    const StatusQueued = 'status_queued';
    const StatusProcessing = 'status_processing';
    const StatusSuccess = 'status_success';
    const StatusFailed = 'status_failed';
    const StatusPartiallyFailed = 'status_partially_failed';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string timestamp
     */
    private $started;

    /**
     * @var string|null random
     */
    private $nonce;

    /**
     * @var int total number of shipment requests
     */
    private $shipmentCount = 0;

    /**
     * @var int number of shipments for which labels are available
     */
    private $successCount = 0;

    /**
     * @var int numer of shipments for wich an error was registered 
     */
    private $failureCount = 0;

    /**
     * @var array|null of array shipment row. May be null if retrieved without shipments 
     */
    private $shipments;


    public function __construct()
    {
        $this->setStarted(new \DateTime());
    }


    public static function fromRow(array $row)
    {
        $instance = new self();
        $instance->id = $row['id'];
        foreach (self::UpdateProps as $key) {
            $instance->$key = $row[$key];
        }
        return $instance;
    }

    public function getDataForUpdate()
    {
        $data = [];
        foreach (self::UpdateProps as $property) {
            $data[$property] = $this->$property;
        }
        return $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTimeInterface|null
     * @throws \Exception
     */
    public function getStarted()
    {
        if (null === $this->started) {
            return null;
        }
        return new \DateTime($this->started);
    }

    /**
     * @param \DateTimeInterface|null $started
     */
    public function setStarted(\DateTimeInterface $dateTime=null)
    {
        $this->started = null === $dateTime
            ? null
            : $dateTime->format('Y-m-d H:i:s');
    }

    /**
     *
     * @return string|null
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     * @return Batch
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
        return $this;
    }

    /**
     * @return int
     */
    public function getShipmentCount()
    {
        return $this->shipmentCount;
    }

    /**
     * @param  int $shipmentCount
     * @return Batch
     */
    public function setShipmentCount($shipmentCount)
    {
        $this->shipmentCount = $shipmentCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * @param  int $successCount
     * @return Batch
     */
    public function setSuccessCount($successCount)
    {
        $this->successCount = $successCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailureCount()
    {
        return $this->failureCount;
    }

    /**
     * @param  int $failureCount
     * @return Batch
     */
    public function setFailureCount($failureCount)
    {
        $this->failureCount = $failureCount;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * @param  array $shipments
     * @return Batch
     */
    public function setShipments($shipments)
    {
        $this->shipments = $shipments;
        return $this;
    }

    public function addShipment(array $shipment)
    {
        $this->shipments[] = $shipment;
        $this->shipmentCount ++;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->shipmentCount == 0) {
            return self::StatusRequest;
        }
        if ($this->successCount == $this->shipmentCount) {
            return self::StatusSuccess;
        }
        if ($this->failureCount == $this->shipmentCount) {
            return self::StatusFailed;
        }
        if ($this->successCount + $this->failureCount == $this->shipmentCount) { // Batch is complete
            return self::StatusPartiallyFailed;
        }

        // Processing is incomplete

        if (is_array($this->getShipments())) {
            // Find highest queued or processing job state
            $jobState = 0;
            foreach ($this->getShipments() as $shipment) {
                if ($shipment['job_state'] <= \ModelExtensionShippingDpdShipment::JobStateProcessing) {
                    $jobState = max($jobState, $shipment['job_state']);
                }
            }
            switch ($jobState) {
            case \ModelExtensionShippingDpdShipment::JobStateQueued:
                return self::StatusQueued;
            case \ModelExtensionShippingDpdShipment::JobStateProcessing:
                return self::StatusProcessing;
            }
        }

        if ($this->successCount > 0 || $this->failureCount > 0) {
            return self::StatusProcessing;
        }
        return self::StatusRequest;
    }

    public function getOrderIds()
    {
        $resultMap = [];
        if (is_array($this->getShipments())) {
            foreach ($this->getShipments() as $shipment) {
                $resultMap[$shipment['order_id']] = true;
            }
        }
        return array_keys($resultMap);
    }

    public function getParcelnumbers()
    {
        $result = [];
        if (is_array($this->getShipments())) {
            foreach ($this->getShipments() as $shipment) {
                $result = array_merge($result, \ModelExtensionShippingDpdShipment::getParcelnumbers($shipment));
            }
        }
        return $result;
    }
}
