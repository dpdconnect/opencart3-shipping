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


use DPD\DpdAuthentication;
use DpdConnect\Common\UrlGenerator;
use DpdConnect\Sdk\ClientInterface;

class OrderViewDataTransformer
{
    /**
     *
     * @var \ModelExtensionShippingDpdShipment 
     */
    private $model_shipment;
    /**
     *
     * @var UrlGenerator  
     */
    private $urlGenerator;
    /**
     *
     * @var \Config  
     */
    private $config;
    /**
     *
     * @var JobStateManager 
     */
    private $jobStateManager;

    /**
     * @var DpdAuthentication
     */
    private $client;

    public function __construct($model_shipment, UrlGenerator $urlGenerator, $config, JobStateManager $jobStateManager, DpdAuthentication $client)
    {
        $this->model_shipment = $model_shipment;
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->jobStateManager = $jobStateManager;
        $this->client = $client;
    }

    /**
     * Transform order data to view data
     *
     * @param  array $order
     * @return array|null
     */
    public function transform($order)
    {
        if (substr($order['shipping_code'], 0, 11) != 'dpdbenelux.') {
            return null;
        }
        $viewData = [];
        $anyLabel = false;
        for ($dpdIsReturn=0; $dpdIsReturn<=1; $dpdIsReturn++) {
            $dpdShipment = $this->model_shipment->getShipment($order['order_id'], $dpdIsReturn);
            if (empty($dpdShipment)) {
                continue;
            }
            $viewData['status'][$dpdIsReturn] = $this->model_shipment->getStatus($dpdShipment);
            if (empty($dpdShipment['mps_id'])) {
                $dpdShipment = $this->jobStateManager->updateJobState($dpdShipment);
                if (!empty($dpdShipment['error']) && $dpdShipment['error'] != 'null') {
                    $viewData['errors'][$dpdIsReturn] = $this->model_shipment->getErrorText($dpdShipment);
                }
            } else {
                $anyLabel = true;
                $dpdLabelData = unserialize($dpdShipment['label_numbers']);
                if (is_array($dpdLabelData)) {
                    $dpdParcelNumbers = [];
                    foreach ($dpdLabelData as $dpdLabelRow) {
                        $dpdParcelNumbers[] = $dpdLabelRow['parcel_number'];
                    }
                    $viewData['labels'][$dpdIsReturn] = implode(' ', $dpdParcelNumbers);
                    $viewData['download_url'][$dpdIsReturn] = $this->urlGenerator->link(
                        'extension/shipping/dpdbenelux/download-label',
                        'order_id=' . $order['order_id']
                        . '&return-only='. $dpdIsReturn,
                        true
                    );
                }
            }
        }
        if (!$anyLabel) {
            $configKey = 'shipping_dpdbenelux_'. substr($order['shipping_code'], 11). '_title';
            $viewData['shipping_method_title'] = $this->config->get($configKey);
        }

        $filter = ['order_id' => $order['order_id']];
        $viewData['shipment_history_url'] = $this->urlGenerator->link('extension/shipping/dpdbenelux/shipments', ['filter' => $filter]);
        $viewData['shipment_history_count'] = $this->model_shipment->countShipments($filter);

        return $viewData;
    }

    /**
     * @param array $order
     * @return bool Wheater the shipment for the order should include customs data
     * @throws \DpdConnect\Sdk\Exceptions\DpdException
     */
    public function isShipmentWithCustoms($order)
    {
        $requestFactory = new LabelRequestFactory($this->config, $this->client);
        $recipient = $requestFactory->getRecipientAddress($order);
        return $requestFactory->includeCustoms($recipient);
    }
}
