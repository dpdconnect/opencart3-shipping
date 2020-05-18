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
use DpdConnect\Sdk\Exception\HttpException;

class LabelResponseTransformer
{
    /**
     * Transform the response from a synchronous label request to Batch and shipment data
     *
     * @param Batch        $batch
     * @param array        $batchResponse
     * @param $labelRequest
     */
    public function transformSync(Batch $batch, array $batchResponse, $labelRequest)
    {
        $shipments = [];
        foreach ($batchResponse['labelResponses'] as $i => $labelResponse) {

            $shipmentData = $this->transformShipment(
                $labelResponse,
                $labelRequest['shipments'][$i],
                $batch->getShipments()[$i]
            );

            if (empty($shipmentData['mps_id'])) {
                $batch->setFailureCount($batch->getFailureCount() + 1);
            } else {
                $batch->setSuccessCount($batch->getSuccessCount() + 1);
            }

            $shipments[] = $shipmentData;
        }
        $batch->setShipments($shipments);
    }

    /**
     * Transform the response from an asynchronous label request to Batch and shipment data
     *
     * @param Batch        $batch
     * @param array        $asyncResponse of Job
     * @param $asyncRequest
     */
    public function transformAsync(Batch $batch, array $asyncResponse, $asyncRequest)
    {
        $shipments = [];
        foreach ($asyncResponse as $i => $job) {
            $shipment = $batch->getShipments()[$i];
            $shipment['job_id'] = $job['jobid'];
            $shipment['job_state'] = $job['state'];
            $shipments[] = $shipment;
        }
        $batch->setShipments($shipments);
    }

    /**
     *
     * @param  array $shipments
     * @return array by order_id.isReturnInt
     */
    private function getShipmentMap(array $shipments)
    {
        $shipmentMap = [];
        foreach ($shipments as $shipment) {
            $key = $shipment['order_id']. '.'. ((int) $shipment['is_return']);
            $shipmentMap[$key] = $shipment;
        }
        return $shipmentMap;
    }

    /**
     *
     * @param  array $job Job
     * @return array|null
     */
    public function transformCallback(array $job)
    {
        // PROBLEM: the trackingInfo may be null but there is no error info either.
        if (empty($job['shipment']['trackingInfo']) && empty($job['error'])
            || empty($job['shipment']['product']['productCode'])
            || empty($job['shipment']['orderId'])
        ) {
            return null;
        }
        $shipmentData = [
            'order_id' => $job['shipment']['orderId'],
            'is_return' => $this->isReturnShipment($job['shipment']),
            'job_id' => $job['jobid'],
        ];
        if (isset($job['state'])) {
            $shipmentData['job_state'] = $job['state'];
        }
        $labelResponse = empty($job['error'])
            ? $job['shipment']['trackingInfo']
            : ['error' => $job['error']];
        return $this->transformShipment($labelResponse, $job['shipment'], $shipmentData);
    }

    /**
     *
     * @param  array $shipmentDTO
     * @return bool
     */
    private function isReturnShipment(array $shipmentDTO)
    {
        return $shipmentDTO['product']['productCode'] == 'RETURN';
    }

    /**
     *
     * @param array $labelResponse May also be TrackingInfo
     * @param       $shipmentRequest May also be Shipment from callback
     * @param       $shipmentData that can be saved in the database
     * @return that shipmentData that can be saved in the database
     */
    private function transformShipment($labelResponse, $shipmentRequest, $shipmentData)
    {
        $parcels = $shipmentRequest['parcels'];
        $labelData = array();
        if (isset($labelResponse['parcelNumbers'])) {
            foreach ($labelResponse['parcelNumbers'] as $j => $labelNumber) {
                $labelData[] = ['parcel_number' => $labelNumber, 'weight' => $parcels[$j]['weight']];
            }
        }
        $shipmentData['mps_id'] = isset($labelResponse['shipmentIdentifier']) ? $labelResponse['shipmentIdentifier'] : null;
        $shipmentData['label_numbers'] = serialize($labelData);
        $shipmentData['label'] = empty($labelResponse['label'])
            ? null
            : base64_decode($labelResponse['label']);
        if (!empty($labelResponse['error'])) {
            $shipmentData['error'] = json_encode($labelResponse['error']);
        }
        return $shipmentData;
    }


}
