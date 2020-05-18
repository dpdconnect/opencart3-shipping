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

use DpdConnect\Sdk\Client;
use DpdConnect\Sdk\Exceptions\DpdException;
use DpdConnect\Label\Entity\Batch;
use DpdConnect\Sdk\DpdConnectClientBuilder;
use DpdConnect\Sdk\Objects\ResourceResponse;

class LabelGenerationManager
{
    /**
     * @var \ModelSaleOrder
     */
    private $model_sale_order;

    /**
     * @var \ModelCatalogProduct
     */
    private $model_product;

    /**
     * @var \ModelLocalisationWeightClass
     */
    private $model_weight_class;

    /**
     * @var \ModelExtensionShippingDpdShipment
     */
    private $model_shipment;

    /**
     * @var \ModelExtensionShippingDpdBatch
     */
    private $model_batch;

    /**
     * @var \ModelExtensionShippingDpdProductExtension
     */
    private $model_dpd_product_extension;

    /**
     * @var \Log
     */
    private $log;

    /**
     * @var \Language
     */
    private $language;

    /**
     * @var \Config
     */
    private $config;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var array
     */
    private $shipmentsToReplace;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        $model_sale_order,
        $model_product,
        $model_weight_class,
        $model_shipment,
        $model_batch,
        $model_dpd_product_extension,
        $dpdauthentication,
        $log,
        $config,
        $language
    ) {
        $this->model_sale_order = $model_sale_order;
        $this->model_product = $model_product;
        $this->model_weight_class = $model_weight_class;
        $this->model_shipment = $model_shipment;
        $this->model_batch = $model_batch;
        $this->model_dpd_product_extension = $model_dpd_product_extension;
        $this->log = $log;
        $this->config = $config;
        $this->language = $language;
        $this->responseTransformer = new LabelResponseTransformer();
        $this->client = DpdConnectClientBuilder::buildAuthenticatedClientUsingDpdAuthentication($dpdauthentication);
        $this->requestFactory = new LabelRequestFactory($this->config, $dpdauthentication);
    }

    /**
     *
     * @param  array $order
     * @return array order with data added that the RequestFactory may need
     */
    private function enrichOrder($order)
    {
        $order['dpdTotals'] = $this->model_sale_order->getOrderTotals($order['order_id']);
        $order['dpdParcelShop'] = $this->model_shipment->getParcelshop($order['order_id']);

        $order['dpdRows'] = [];
        $totalWeightInDecagrams = 0;
        foreach ($this->model_sale_order->getOrderProducts($order['order_id']) as $orderRow) {
            $orderRow['dpdProduct'] = $this->model_product->getProduct($orderRow['product_id']);
            $orderRow['dpdAttributes'] = $this->model_product->getProductAttributes($orderRow['product_id']);
            $orderRow['dpdProductExtensions'] = $this->model_dpd_product_extension->find($orderRow['product_id']);
            $orderRow['dpdWeightInDecagrams'] = $this->getProductWeightInDecagrams($orderRow, $orderRow['dpdProduct']);
            $totalWeightInDecagrams += $orderRow['dpdWeightInDecagrams'];
            $order['dpdRows'][] = $orderRow;
        }
        $order['dpdWeightInDecagrams'] = $totalWeightInDecagrams;

        return $order;
    }

    private function getProductWeightInDecagrams($orderRow, $product_info)
    {
        $weight = $product_info['weight'];
        if ($weight != 0) {
            $weightClassId = $product_info['weight_class_id'];
            $weightClass = $this->model_weight_class->getWeightClass($weightClassId);

            // Each weight class has a value, this is the amount you have to devide the weight to get the same amount in KG
            $weightInKG = $weight / $weightClass['value'];
        } else {
            $weightInKG = (float) str_replace(',', '.', $this->config->get('shipping_dpdbenelux_weight_default'));
        }

        $weightInDecagrams = $weightInKG * 100;

        return $weightInDecagrams * $orderRow['quantity'];
    }

    /**
     *
     * @param  array $selectedOrderIds
     * @param  int   $parcelCount
     * @param  bool  $includeReturnLabel
     * @param  bool  $returnLabelOnly
     * @param  bool  $replaceExisting
     * @return Batch|null Batch if shipments are created, otherwise return null
     * @throws \Exception
     */
    public function generateLabels($selectedOrderIds, $parcelCount, $includeReturnLabel, $returnLabelOnly, $replaceExisting)
    {
        $labelsRequired = [];
        $this->errors = [];
        $this->batch = new Batch();

        $this->shipmentsToReplace = [];
        try {
            foreach($selectedOrderIds as $orderId) {
                $order = $order = $this->model_sale_order->getOrder($orderId);

                // Ignore all orders that don't start with dpdbenelux as their shipping code
                if(strpos($order['shipping_code'], 'dpdbenelux') !== 0) {
                    continue;
                }
                $order = $this->enrichOrder($order);

                if(!$returnLabelOnly) {
                    $this->processOrder($order, false, $replaceExisting, $parcelCount);
                }

                if($includeReturnLabel || $returnLabelOnly) {
                    $this->processOrder($order, true, $replaceExisting, $parcelCount);
                }
            }

            if ($this->batch->getShipmentCount() == 0) {
                return;
            }

            $this->persistBatchAndShipments();

            if ($this->config->get('shipping_dpdbenelux_asynchronous')
                && $this->batch->getShipmentCount() >= $this->config->get('shipping_dpdbenelux_asynchronous_from')
            ) {
                $this->requestLabelsAsync($parcelCount);
            } else {
                $this->requestLabels($parcelCount);
            }
            // Save shipments to the database, collect errors
            foreach ($this->batch->getShipments() as $i => $shipmentData) {
                unset($shipmentData['current']); // prevent race conditions

                $this->model_shipment->updateShipment($shipmentData);
                $vndErrorJson = $shipmentData['error'];
                if (null !== $vndErrorJson && 'null' != $vndErrorJson) {
                    $vndEror = json_decode($vndErrorJson, true);
                    $this->errors = array_merge($this->errors, $this->transformVndError($vndEror, $shipmentData['is_return'], $shipmentData['order_id']));
                }
            }
        } catch (DpdException $e) {
            $message = $this->language->get('error_login'). ' :';
            $message .= strlen($e->getMessage()) == 0 ? get_class($e) : $e->getMessage();
            $error = ['class' => get_class($e), 'code' => $e->getCode(), 'message' => $message];
            $this->errors[] = $error;
            if ($this->batch->getShipmentCount() > 0) {
                $this->setErrorOnShipments(json_encode($error, true), true);
            }
        } catch (\Exception $e) {
            $this->log->write((string)$e);
            $this->errors[] = ['message' => 'Internal Server Error'];
        }

        $this->model_batch->updateBatch($this->batch);

        return $this->batch;
    }

    /**
     * Set the error json on all the shipments from $this->batch
     *
     * @param string $errorJson
     * @param bool   $save      Wheather to save the shipments from $this->batch to the database
     */
    private function setErrorOnShipments($errorJson, $save=false)
    {
        $shipments = [];
        foreach ($this->batch->getShipments() as $shipment) {
            $shipment['error'] = $errorJson;
            $shipments[] = $shipment;
            if ($save) {
                $update = ['error' => $errorJson, 'dpd_shipment_id' => $shipment['dpd_shipment_id']];
                $this->model_shipment->updateShipment($update);
            }
        }
        $this->batch->setShipments($shipments);
        $this->batch->setFailureCount($this->batch->getShipmentCount());
    }

    /**
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate the order and if valid, add a schipment to $this->batch.
     * If not valid add error(s) to $this->errors
     *
     * @param array $order           Enriched order
     * @param bool  $isReturn
     * @param bool  $replaceExisting
     * @param int   $parcelCount
     */
    private function processOrder(array $order, $isReturn, $replaceExisting, $parcelCount)
    {
        $previousShipment = $this->model_shipment->getShipment($order['order_id'], $isReturn);
        if ($replaceExisting || empty($previousShipment)) {
            $valid = true;
            $recipient = $this->requestFactory->getRecipientAddress($order);
            $includeCustoms = $this->requestFactory->includeCustoms($recipient);
            if ($includeCustoms) {
                if (!$this->validateCustomsMaxima($order, $previousShipment, $isReturn, $parcelCount)) {
                    return;
                }
                $valid = $this->validateHsc($order, $isReturn) && $valid;
                $valid = $this->validateOriginCountry($order, $isReturn) && $valid;
            }
            $valid = $this->validateRecipientZipcode($order, $isReturn) && $valid;
            $valid = $this->validateWeight($order, $isReturn) && $valid;
            if (!$valid) {
                return;
            }

            $newShipment = [
                'order' => $order,
                'order_id' => $order['order_id'],
                'is_return' => $isReturn,
                'is_current' => true,
                'mps_id' => null,
                'label' => null,
                'error' => 'null',
                'job_id' => null,
                'job_state' => null,
                'label_numbers' => serialize([]),
            ];
            $this->batch->addShipment($newShipment);

            if (!empty($previousShipment)) {
                $this->shipmentsToReplace[] = $previousShipment;
            }
        }
    }

    /**
     * Insert $this->batch and its shipments in the database
     *
     * @throws \Exception
     */
    private function persistBatchAndShipments()
    {
        $this->model_batch->startTransaction();
        try {
            foreach ($this->shipmentsToReplace as $shipmentData) {
                $update = [
                    'dpd_shipment_id' => $shipmentData['dpd_shipment_id'],
                    'is_current' => false,
                ];
                $this->model_shipment->updateShipment($update);
            }
            $this->model_batch->createBatch($this->batch);
            $shipments = [];
            foreach ($this->batch->getShipments() as $shipment) {
                $shipment['batch_id'] = $this->batch->getId();
                $shipmentId = $this->model_shipment->createShipment($shipment);
                $shipment['dpd_shipment_id'] = $shipmentId;
                $shipments[] = $shipment;
            }
            $this->batch->setShipments($shipments);
            $this->model_batch->commit();
        } catch (\Exception $e) {
            $this->model_batch->rollback();
            throw $e;
        }
    }

    /**
     * Perform a synchronous label request
     *
     * @param  int $parcelCount
     * @throws DpdException
     */
    private function requestLabels($parcelCount)
    {
        try {
            /** @var ResourceResponse $labels */
            $labelRequest = $this->requestFactory->getLabelRequest($this->batch, $parcelCount);
            $response = $this->client->getShipment()->create($labelRequest);
            $statusCode = $response->getStatus();

            if ($statusCode < 200 || $statusCode >= 300) {
                return $this->addErrorsFrom($statusCode, $response->getContent());
            }
            $this->responseTransformer->transformSync($this->batch, $response->getContent(), $labelRequest);
        } catch (\Throwable $exception) {
            var_dump($exception); die();
        }
    }

    /**
     * Perform an asynchonous label request
     *
     * @param  int $parcelCount
     * @throws DpdException
     */
    private function requestLabelsAsync($parcelCount)
    {
        try {
            $this->batch->setNonce($this->generateNonce());
            $labelRequest = $this->requestFactory->getAsyncLabelRequest($this->batch, $parcelCount);
            $response = $this->client->getShipment()->createAsync($labelRequest);
            $statusCode = $response->getStatus();

            if ($statusCode < 200 || $statusCode >= 300) {
                return $this->addErrorsFrom($statusCode, $response->getContent());
            }
            $this->responseTransformer->transformAsync($this->batch, $response->getContent(), $labelRequest);
        } catch (\Throwable $exception) {
            var_dump($exception); die();
        }

    }

    /**
     * Generate a unique random string for $this->batch that will be used by the callback endpoint
     * for security and retrieving the batch
     *
     * @return string
     */
    private function generateNonce()
    {
        if (function_exists('random_bytes')) {
            try {
                return bin2hex(random_bytes(16));
            } catch (\Exception $e) {
                // continue as if the fucntion did not exist
            }
        }
        $bytes = '';
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strong = null;
            $bytes = openssl_random_pseudo_bytes(16, $strong);
            if ($strong) {
                return bin2hex($bytes);
            }
        }
        $prefix = $bytes. $this->batch->getId(). implode('', $this->batch->getOrderIds());
        return str_shuffle(sha1(uniqid($prefix, true)));
    }

    private function validateRecipientZipcode($order, $isReturnLabel)
    {
        if ($order['shipping_code'] == 'dpdbenelux.parcelshop') {
            if (empty($order['payment_postcode'])) {
                $this->errors[] = ['message' => $this->language->get('error_payment_zipcode_required'), 'order_id' => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
                return false;
            }
            return true;
        }
        if (empty($order['shipping_postcode'])) {
            $this->errors[] = ['message' => $this->language->get('error_shipping_zipcode_required'), 'order_id' => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
            return false;
        }
        return true;
    }

    private function validateWeight($order, $isReturnLabel)
    {
        $valid = true;
        foreach ($order['dpdRows'] as $i => $orderRow) {
            if (((int) round($orderRow['dpdWeightInDecagrams'])) <= 0) {
                $message = sprintf($this->language->get('error_product_weight_too_low'), $orderRow['name'], $i+1);
                $this->errors[] = ['message' => $message, 'order_id' => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
                $valid = false;
            }
        }
        return $valid;
    }

    private function validateHsc($order, $isReturnLabel)
    {
        $valid = true;
        foreach ($order['dpdRows'] as $i => $orderRow) {
            if (strlen($this->requestFactory->getAttributeOrExtensionValue($orderRow, 'export_hsc')) == 0) {
                $message = sprintf($this->language->get('error_hsc_missing'), $orderRow['name'], $i + 1);
                $this->errors[] = ['message'       => $message, 'order_id'      => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
                $valid = false;
            }
        }
        return $valid;
    }

    private function validateOriginCountry($order, $isReturnLabel)
    {
        $valid = true;
        foreach ($order['dpdRows'] as $i => $orderRow) {
            if (strlen($this->requestFactory->getAttributeOrExtensionValue($orderRow, 'export_origin_country')) == 0) {
                $message = sprintf($this->language->get('error_origin_country_missing'), $orderRow['name'], $i + 1);
                $this->errors[] = ['message'       => $message, 'order_id'      => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
                $valid = false;
            }
        }
        return $valid;
    }

    private function validateCustomsMaxima($order, $previousShipment, $isReturnLabel, $parcelCount)
    {
        if (!empty($previousShipment['mps_id'])) {
            $this->errors[] = ['message' => $this->language->get('error_customs_only_once'), 'order_id' => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
            return false;
        }
        if ($parcelCount > 1) {
            $this->errors[] = ['message' => $this->language->get('error_customs_multiple_parcels'), 'order_id' => $order['order_id'], 'isReturnLabel' => $isReturnLabel];
            return false;
        }
        return true;
    }

    /**
     * Add the error from $body to (the right) shipment(s)
     *
     * @param int    $status Http status code
     * @param string $body
     */
    private function addErrorsFrom($status, $body)
    {
        $decoded = json_decode($body, true);
        if (empty($decoded['message'])) {
            $error = ['message' => 'HTTP Error '. $status. ' got '. $body];
            $this->setErrorOnShipments(json_encode($error));
            return;
        }
        // TODO: embedded errors with dataPath should only be set on the respective shipment
        $this->setErrorOnShipments($body);
    }

    /**
     *
     * @param  string $vndErrorJson json encoded
     * @param  bool   $isReturn
     * @param  string $orderId
     * @param  array  $parcels
     * @return array of ['message' => <message>, 'order_id' => $orderId, 'isReturnLabel' => $isReturnLabel],
     */
    private function transformVndError($vndError, $isReturnLabel=null, $orderId=null)
    {
        if (isset($vndError['_embedded']['errors'])) {
            $errors = [];
            foreach ($vndError['_embedded']['errors'] as $error) {
                $error['order_id'] = $orderId;
                $error['isReturnLabel'] = $isReturnLabel;
                $errors[] = $error;
            }
            return $errors;
        }

        if (isset($orderId)) {
            $vndError['order_id'] = $orderId;
        }
        if (isset($isReturnLabel)) {
            $vndError['isReturnLabel'] = $isReturnLabel;
        }
        return [ $vndError ];
    }
}
