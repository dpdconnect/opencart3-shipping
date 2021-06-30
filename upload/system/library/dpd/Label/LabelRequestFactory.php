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
use DPD\DpdConfiguration;
use DpdConnect\Label\Entity\Batch;
use DpdConnect\Sdk\DpdConnectClientBuilder;
use DpdConnect\Sdk\Client;
use DpdConnect\Sdk\Common\HttpClient;
use DpdConnect\Sdk\Exceptions\DpdException;

class LabelRequestFactory
{
    const ProductCodeReturn = 'RETURN';

    /**
     * @var \Config  
     */
    private $config;

    /**
     * @var ClientInterface
     */
    private $client;
    /**
     *
     * @var array 
     */
    private $countriesList;

    public function __construct(\Config $config, DpdAuthentication $dpdAuth)
    {
        $this->config = $config;
        $builder = new DpdConnectClientBuilder();
        $this->client = $builder::buildAuthenticatedClientUsingDpdAuthentication($dpdAuth);
    }

    /**
     * @param Batch $batch
     * @param int $parcelCount
     * @return array
     * @throws DpdException
     */
    public function getLabelRequest(Batch $batch, $parcelCount)
    {
        return [
            'printOptions' => [
                'printerLanguage' => 'PDF',
                'paperFormat' => $this->config->get('shipping_dpdbenelux_paper_format'),
                'verticalOffset' =>  0,
                'horizontalOffset' => 0,
            ],
            'createLabel' => true,
            'shipments' => $this->getShipments($batch, $parcelCount),
        ];
    }

    /**
     *
     * @param Batch $batch
     * @param int $parcelCount
     * $return array
     * @return array
     * @throws DpdException
     */
    public function getAsyncLabelRequest(Batch $batch, $parcelCount)
    {
        return [
            'callbackURI' => $this->getCallbackUri($batch->getNonce()),
            'label' => $this->getLabelRequest($batch, $parcelCount),
        ];
    }

    /**
     *
     * @param  $nonce
     * @return string The uri for the callback
     */
    private function getCallbackUri($nonce)
    {
        return HTTPS_CATALOG
            . 'index.php?route=extension/shipping/dpdbenelux/asyncShipmentCallback&nonce='
            . $nonce;
    }

    /**
     *
     * @param Batch $batch
     * @param int $parcelCount
     * $return array of shipment
     * @return array
     * @throws DpdException
     */
    private function getShipments(Batch $batch, $parcelCount)
    {
        $shipments = [];
        foreach($batch->getShipments() as $shipmentData) {
            $shipments[] = $this->getShipment($shipmentData, $parcelCount);
        }
        return $shipments;
    }

    /**
     *
     * @param  array $orderData
     * @param  bool  $isReturnLabel
     * @param  int   $parcelCount
     * @return array shipment
     * @throws DpdException
     */
    private function getShipment($shipmentData, $parcelCount)
    {
        $orderData = $shipmentData['order'];
        $recipient = $this->getRecipientAddress($orderData);
        $includeCustoms = $this->includeCustoms($recipient);
        $shipmentDTA = [
            'sendingDepot' => $this->config->get('shipping_dpdbenelux_sending_depot'),
            'sender' => $this->getSenderAddress($includeCustoms),
            'receiver' => $recipient,
            'parcels' => $this->getParcels($orderData, $parcelCount),
            'product' => $this->getProductAndServices($orderData, $shipmentData['is_return']),
            'notifications' => $this->getNotifications($orderData, $shipmentData['is_return']),
            'orderId' => $orderData['order_id'],
            'weight' => (int) round($orderData['dpdWeightInDecagrams']),
        ];
        if ($includeCustoms) {
            $shipmentDTA['customs'] = $this->getCustoms($shipmentData, $shipmentDTA);
        }
        return $shipmentDTA;
    }

    /**
     *
     * @param  array $recipient
     * @return bool Wheather to include customs data
     * @throws DpdException
     */
    public function includeCustoms($recipient)
    {
        return !$this->isInSingleMarket($recipient['country']);
    }

    private function getParcels($orderData, $parcelCount)
    {
        $weightInDecagrams = $orderData['dpdWeightInDecagrams'];
        $parcels = array();

        for($i = 0; $i < $parcelCount; $i++) {
            $parcels[] = [
                'customerReferences' => [$orderData['order_id']],
                'weight' => (int) round($weightInDecagrams / $parcelCount),
            ];
        }

        return $parcels;
    }

    private function getProductAndServices($orderData, $isReturnLabel)
    {
        if($isReturnLabel) {
            return ['productCode' => self::ProductCodeReturn];
        }
        $productAndServiceData = [];

        $product = 'CL';

        if(!$isReturnLabel) {
            if ($orderData['shipping_code'] == 'dpdbenelux.express10') {
                $product = 'E10';
            }
            if ($orderData['shipping_code'] == 'dpdbenelux.express12') {
                $product = 'E12';
            }
            if ($orderData['shipping_code'] == 'dpdbenelux.guarantee18') {
                $product = 'E18';
            }

            if ($orderData['shipping_code'] == 'dpdbenelux.parcelshop') {
                $productAndServiceData['parcelshopId'] = $orderData['dpdParcelShop']['parcelshop_id'];
            }

            if (($orderData['shipping_code'] == 'dpdbenelux.saturday' || $orderData['shipping_code'] == 'dpdbenelux.classic_saturday')) {
                $productAndServiceData['saturdayDelivery'] = true;
            }

            if (($orderData['shipping_code'] == 'dpdbenelux.saturday' || $orderData['shipping_code'] == 'dpdbenelux.predict')) {
                $productAndServiceData['homeDelivery'] = true;
            }
            $productAndServiceData['ageCheck'] = $this->checkIfAgeCheck($orderData);
        }

        $productAndServiceData['productCode'] = $product;

        return $productAndServiceData;
    }

    private function checkIfAgeCheck($orderData)
    {
        foreach ($orderData['dpdRows'] as $i => $row) {
            if($this->getAttributeOrExtensionValue($row, 'age_check_attribute')) {
                return true;
            }
        }

        return false;
    }

    private function getNotifications($orderData, $isReturnLabel)
    {
        if ($isReturnLabel) {
            return [];
        }

        // Predict and Saturday are the only two B2C methods with the predict service
        if ($orderData['shipping_code'] == 'dpdbenelux.predict' || $orderData['shipping_code'] == 'dpdbenelux.saturday') {
            return [ array(
                'subject' => 'predict',
                'channel' => 'EMAIL',
                'value'   => $orderData['email'],
            ) ];
        }

        if ($orderData['shipping_code'] == 'dpdbenelux.parcelshop') {
            return [ array(
                "subject" => "parcelshop",
                'channel' => 'EMAIL',
                'value'   => $orderData['email']
            ) ];
        }

        return [];
    }

    /**
     *
     * @param  $orderData
     * @return array
     * @throws DpdException
     */
    public function getRecipientAddress($orderData)
    {
        if($orderData['shipping_code'] == 'dpdbenelux.parcelshop') {
            $data['name1'] = $orderData['payment_firstname'] . ' ' . $orderData['payment_lastname'];
            $data['name2'] = $orderData['payment_company'];
            $data['street'] = $orderData['payment_address_1'] . ' ' . $orderData['payment_address_2'];
            $data['housenumber'] = '';
            $data['postalcode'] = $orderData['payment_postcode'];
            $data['city'] = $orderData['payment_city'];
            $data['country'] = $orderData['payment_iso_code_2'];
            $data['phoneNumber'] = $orderData['telephone'];
        }
        else
        {
            $data['name1'] = $orderData['shipping_firstname'] . ' ' . $orderData['shipping_lastname'];
            $data['name2'] = $orderData['shipping_company'];
            $data['street'] = $orderData['shipping_address_1'] . ' ' . $orderData['shipping_address_2'];
            $data['housenumber'] = '';
            $data['postalcode'] = $orderData['shipping_postcode'];
            $data['city'] = $orderData['shipping_city'];
            $data['country'] = $orderData['shipping_iso_code_2'];
            $data['phoneNumber'] = $orderData['telephone'];

            if($orderData['shipping_code'] == 'dpdbenelux.guarantee18' 
                || $orderData['shipping_code'] == 'dpdbenelux.express12' 
                || $orderData['shipping_code'] == 'dpdbenelux.express10'
            ) {
                $data['contact'] = $data['name1'];
            }
        }
        $data['commercialAddress'] = strlen($this->getVatNumber($orderData)) > 0;
        $data['email'] = $orderData['email'];

        return $data;
    }

    public function getSenderAddress($includeCustoms)
    {
        $data = array();
        $data['name1'] = $this->config->get('shipping_dpdbenelux_sender_company_name');
        $data['street'] = $this->config->get('shipping_dpdbenelux_sender_street');
        $data['postalcode'] = $this->config->get('shipping_dpdbenelux_sender_postal_code');
        $data['city'] = $this->config->get('shipping_dpdbenelux_sender_place');
        $data['country'] = $this->config->get('shipping_dpdbenelux_sender_country_code');
        $data['phoneNumber'] = $this->config->get('shipping_dpdbenelux_sender_phone');
        $data['email'] = $this->config->get('config_email');
        $data['commercialAddress'] = true;
        if ($includeCustoms) {
            $data['eorinumber'] = $this->config->get('shipping_dpdbenelux_consignor_eori_number');
        }
        return $data;
    }

    public function getShippingListCarrierCode($shippingCode)
    {
        if(strpos($shippingCode, 'dpdbenelux') !== 0) {
            return $shippingCode;
        }

        $shippingMethod = str_replace('dpdbenelux.', '', $shippingCode);

        switch ($shippingMethod) {
            case 'guarantee18':
                return 'DPD 18';
            case 'classic_saturday':
                return'DPD B2B Sat';
            case 'saturday':
                return 'DPD B2C Sat';
            case 'express12':
                return 'DPD 12';
            case 'express10':
                return 'DPD 10';
            default:
                return $shippingMethod;
        }
    }

    private function getCustoms($shipmentData, $shipmentDTA)
    {
        $orderData = $shipmentData['order'];
        return [
            // Incoterms  terms for shipment.
            'terms' => 'DAP', // TODO: verplicht volgens swagger contract

            // SALE, RETURN, GIFT
            'reasonForExport' => ($shipmentDTA['product']['productCode'] == self::ProductCodeReturn ? 'RETURN' : 'SALE'), // ? maybe a replacement...

            // value of invoice position with two decimal digits without separator
            'totalAmount' => ((float) $orderData['total']), // ? maybe should be excluding shipping, VAT. Or maybe should use sum of totalAmount of all customsLines?

            'totalCurrency' => $orderData['currency_code'],
            'consignee' => $this->getConsignee($shipmentData, $shipmentDTA),
            'consignor' => $this->getConsignor($shipmentData, $shipmentDTA),
            'customsLines' => $this->getCustomsLines($shipmentData, $shipmentDTA),
        ];
    }

    private function getConsignee($shipmentData, $shipmentDTA)
    {
        $orderData = $shipmentData['order'];
        $address = $shipmentDTA['receiver'];
        $address['contact'] = $address['phoneNumber'];
        $address['email'] = $orderData['email'];

        $address['vatNumber'] = $this->getVatNumber($orderData);

        return $address;
    }

    private function getVatNumber(array $orderData)
    {
        $fieldId = $this->config->get('shipping_dpdbenelux_export_vat_number_source');
        if (!empty($orderData['payment_custom_field'][$fieldId])) {
            return $orderData['payment_custom_field'][$fieldId];
        }
        if (!empty($orderData['shipping_custom_field'][$fieldId])) {
            return $orderData['shipping_custom_field'][$fieldId];
        }
        if (!empty($orderData['custom_field'][$fieldId])) {
            return $orderData['custom_field'][$fieldId];
        }
        return null;
    }

    private function getConsignor($shipmentData, $shipmentDTA)
    {
        $address = $shipmentDTA['sender'];
        return $address;
    }

    private function getCustomsLines($shipmentData, $shipmentDTA)
    {
        $lines = [];
        foreach ($shipmentData['order']['dpdRows'] as $i => $row) {
            $lines[] = [
                // description of the content
                'description' => $row['name']. ' '. $row['model'],
                // customs tarif number
                'harmonizedSystemCode' => $this->getAttributeOrExtensionValue($row, 'export_hsc'),
                // countrycode of invoice origin in ISO 3166-1 alpha-2 format
                'originCountry' => $this->getAttributeOrExtensionValue($row, 'export_origin_country'),
                    // number of items in the parcel
                'quantity' => (int) $row['quantity'],
                // gross weight in decagram (10 Grams) units without decimal delimiter
                'grossWeight' => (int) round($row['dpdWeightInDecagrams']),
                //  value of the article
                'totalAmount' => (float) $this->getExportValue($row),
                // article ordering (line number of invoice)
                'customsLineNumber' => $i,
            ];
        }

        return $lines;
    }

    /**
     *
     * @param  $row Enriched Product
     * @return mixed the value of the products described by $row (product value * quantity)
     */
    private function getExportValue($row)
    {
        $value = $this->getAttributeOrExtensionValue($row, 'export_value');
        if (empty($value)) {
            return $row['total'];
        }
        return str_replace(',', '.', $value) * $row['quantity'];
    }

    /**
     *
     * @param  $row Enriched Product
     * @param  $field Name of Extension Column
     * @return mixed
     */
    public function getAttributeOrExtensionValue($row, $field)
    {
        $attributeId = $this->config->get('shipping_dpdbenelux_'. $field. '_source');

        if (!empty($attributeId)) {
            foreach ($row['dpdAttributes'] as $attribute) {
                if ($attribute['attribute_id'] == $attributeId) {
                    return current($attribute['product_attribute_description'])['text'];
                }
            }
        }

        if (empty($attributeId) && !empty($row['dpdProductExtensions'][$field])) {
            return $row['dpdProductExtensions'][$field];
        }

        return $this->config->get('shipping_dpdbenelux_'. $field. '_default');
    }

    /**
     * @param string $countryCode
     * @return bool
     * @throws DpdException
     * @throws \DpdConnect\Sdk\Exceptions\DpdException
     */
    private function isInSingleMarket($countryCode)
    {
        if (!isset($this->countriesList)) {
            $this->countriesList = $this->client->getCountries()->getList();
        }
        foreach ($this->countriesList as $country) {
            if ($country['country'] == $countryCode) {
                return $country['singleMarket'];
            }
        }
        return false;
    }
}