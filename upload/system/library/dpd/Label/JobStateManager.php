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

namespace DpdConnect\Label;

use DpdConnect\Sdk\ClientInterface;
use DpdConnect\Label\Entity\Batch;
use DpdConnect\Sdk\DpdConnectClientBuilder;
use DpdConnect\Sdk\Exceptions\DpdException;

class JobStateManager
{
    /**
     * @var \ModelExtensionShippingDpdShipment 
     */
    private $model_shipment;

    /**
     * @var \ModelExtensionShippingDpdBatch 
     */
    private $model_batch;

    /**
     * @var \Log 
     */
    private $log;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(
        $model_shipment,
        $model_batch,
        $log,
        $dpdauthentication
    ) {
        $this->model_shipment = $model_shipment;
        $this->model_batch = $model_batch;
        $this->log = $log;
        $this->responseTransformer = new LabelResponseTransformer();
        $this->client = DpdConnectClientBuilder::buildAuthenticatedClientUsingDpdAuthentication($dpdauthentication);
    }

    /**
     * Updates jobstate of $shipmentData
     *
     * @param array $shipmentData
     * @param Batch|null $batch Will be updated if supplied
     * @return array
     * @throws DpdException
     */
    public function updateJobState(array $shipmentData, Batch $batch=null)
    {
        if (null === $shipmentData['job_id']
            || $shipmentData['job_state'] > \ModelExtensionShippingDpdShipment::JobStateProcessing
            || ($shipmentData['error'] !== null && $shipmentData['error'] != 'null')
            || !empty($shipmentData['mps_id'])
        ) {
            return $shipmentData;
        }

        $job = $this->retrieveJob($shipmentData['job_id']);
        if (null === $job || $job['state'] == $shipmentData['job_state']) {
            return $shipmentData; // No change
        }

        $update = ['job_state' => $job['state'], 'dpd_shipment_id' => $shipmentData['dpd_shipment_id']];
        $shipmentData['job_state'] = $job['state'];
        if ($job['state'] > \ModelExtensionShippingDpdShipment::JobStateProcessing) {
            // Job state changed to a final stage, but shipment has no DPD shipment identifier and no error
            // For example because callback did not succeed
            $shipmentData['error'] = $update['error'] = $this->getShipmentError(
                $job['state'] == \ModelExtensionShippingDpdShipment::JobStateFailed
                    ? $job['stateMessage']
                    : 'Job successfull but no DPD shipment identifier'
            );

            if (null === $batch) {
                $batch = $this->model_batch->findBatch($shipmentData['batch_id']);
            }
            $batch->setFailureCount($batch->getFailureCount() + 1);
            $this->model_batch->updateBatch($batch);
        }
        $this->model_shipment->updateShipment($update);
        return $shipmentData;
    }

    /**
     * Retrieve a jon from the DPD Connect Api
     *
     * @param  $jobId
     * @return array|null The job or null if an error
     * @throws DpdException
     */
    private function retrieveJob($jobId)
    {
        return $this->client->getJob()->getState($jobId);
    }

    /**
     * Return Json encoded error object 
     */
    private function getShipmentError($message)
    {
        return json_encode(['message' => $message]);
    }
}
