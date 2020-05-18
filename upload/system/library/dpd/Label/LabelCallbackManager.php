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

use DpdConnect\Label\Entity\Batch;
use DpdConnect\Sdk\Client;
use DpdConnect\Sdk\Common\Authentication;
use DpdConnect\Sdk\Common\AuthenticatedHttpClient;
use DPD\DpdConfiguration;
use DpdConnect\Sdk\DpdConnectClientBuilder;

class LabelCallbackManager
{
    /**
     *
     * @var \ModelExtensionShippingDpdShipment 
     */
    private $model_shipment;
    /**
     *
     * @var \ModelExtensionShippingDpdBatch 
     */
    private $model_batch;
    /**
     *
     * @var \Log 
     */
    private $log;
    /**
     * @var \DPD\DpdAuthentication 
     */
    private $dpdauthentication;

    /**
     * @var Client
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
        $this->dpdauthentication = $dpdauthentication;
        $this->responseTransformer = new LabelResponseTransformer();
        $this->client = DpdConnectClientBuilder::buildAuthenticatedClientUsingDpdAuthentication($dpdauthentication);
    }

    /**
     *
     * @param Batch $batch
     * @param array $data
     */
    public function processCallback($batch, $data)
    {
        $shipmentData = $this->responseTransformer->transformCallback($data);
        if (null === $shipmentData) {
            return $this->logError('Invalid shipment data', $batch, $data);
        }

        $shipment = $this->model_shipment->selectWithBatchIdOrderIdIsReturn(
            $batch->getId(),
            $shipmentData['order_id'],
            $shipmentData['is_return']
        );
        if (empty($shipment)) {
            return $this->logError('Shipment not found', $batch, $data);
        }
        if (empty($shipment['job_id'])) {
            return $this->logError('Shipment missing job_id', $batch, $data);
        }
        if (empty($shipmentData['job_id']) || $shipment['job_id'] != $shipmentData['job_id']) {
            // jobs ids are assigned using their index in async response, may not be fully reliable
            return $this->logError('wrong job id', $batch, $data);
        }

        $shipmentData['dpd_shipment_id'] = $shipment['dpd_shipment_id'];
        // For us the job is successfull, prevent further fetching of job state
        $shipmentData['job_state'] = 4; // \ModelExtensionShippingDpdShipment::JobStateSuccess; but that is only in admin
        if (!empty($shipmentData['mps_id']) && $shipmentData['mps_id'] != $shipment['mps_id']) {
            $batch->setSuccessCount($batch->getSuccessCount() + 1);
        }
        if (!empty($shipmentData['error']) && $shipmentData['error'] != $shipment['error']) {
            $batch->setFailureCount($batch->getFailureCount() + 1);
        }

        $this->model_batch->startTransaction();
        try {
            $this->model_shipment->updateShipment($shipmentData);
            $this->model_batch->updateBatch($batch);
            $this->model_batch->commit();
        } catch (\Exception $e) {
            $this->logError('Database error '. $e->getMessage(), $batch, $data);
        }

        if (empty($shipmentData['mps_id'])) {
            return;
        }

        $this->retrieveAndStoreLabel($shipmentData);
    }

    /**
     * Retrieve label and store it in the db
     */
    public function retrieveAndStoreLabel($shipmentData)
    {
        $labelNumbers = unserialize($shipmentData['label_numbers']);
        $parcelNumber = current($labelNumbers)['parcel_number'];

        $response = $this->client->getParcel()->getLabel($parcelNumber);

        $this->model_shipment->updateShipment(
            [
                'dpd_shipment_id' => $shipmentData['dpd_shipment_id'],
                'label' => $response,
            ]
        );
        return $response;
    }

    private function logError($message, $batch, $data)
    {
        $this->log->write(
            'asyncShipmentCallback '
            . $message
            . ' batch id: '. $batch->getId()
            . ' body: ' . json_encode($data)
        );
    }
}
