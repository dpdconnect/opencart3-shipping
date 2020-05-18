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

use DpdConnect\Sdk\DpdConnectClientBuilder;
use DpdConnect\ParcelShop\Client\GoogleMapsClient;
use DpdConnect\Label\LabelCallbackManager;

class ControllerExtensionShippingDpdBenelux extends Controller
{

    public function index()
    {

    }

    public function translations()
    {
        $this->load->language('extension/shipping/dpdbenelux');

        $json = array();
        $json['dpd_click_to_load'] = $this->language->get('dpd_click_to_load');
        $json['dpd_click_to_change'] = $this->language->get('dpd_click_to_change');
        $json['dpd_on_map'] = $this->language->get('dpd_on_map');
        $json['dpd_using_list'] = $this->language->get('dpd_using_list');
        $json['dpd_ship_here'] = $this->language->get('dpd_ship_here');
        $json['dpd_more_information'] = $this->language->get('dpd_more_information');
        $json['monday'] = $this->language->get('monday');
        $json['tuesday'] = $this->language->get('tuesday');
        $json['wednesday'] = $this->language->get('wednesday');
        $json['thursday'] = $this->language->get('thursday');
        $json['friday'] = $this->language->get('friday');
        $json['saturday'] = $this->language->get('saturday');
        $json['sunday'] = $this->language->get('sunday');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveParcelshop()
    {
        $this->load->language('extension/shipping/dpdbenelux');

        $json = array();

        if (!isset($this->request->post['selected_parcelshop_id']) || $this->request->post['selected_parcelshop_id'] == '') {
            $this->session->data['error'] = $this->language->get('error_no_parcelshop_selected');
            $json['redirect'] = $this->url->link('checkout/shipping_method');

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->session->data['dpd']['parcelshop_id'] = $this->request->post['selected_parcelshop_id'];
        $this->session->data['dpd']['parcelshop_company'] = $this->request->post['selected_parcelshop_company'];
        $this->session->data['dpd']['parcelshop_street'] = $this->request->post['selected_parcelshop_street'];
        $this->session->data['dpd']['parcelshop_zipcode'] = $this->request->post['selected_parcelshop_zipcode'];
        $this->session->data['dpd']['parcelshop_city'] = $this->request->post['selected_parcelshop_city'];
        $this->session->data['dpd']['parcelshop_country'] = $this->request->post['selected_parcelshop_country'];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function parcelshop()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('localisation/country');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();

        /** @var Log $log */
        $log = $this->registry->get('log');

        if (empty($this->request->post['postcode'])) {
            $log->write('ControllerExtensionShippingDpdBenelux::parcelshop missing postcode');
            return $this->errorResponse($this->language->get('error_invalid_address'));
        }
        if (empty($this->request->post['countryId'])) {
            $log->write('ControllerExtensionShippingDpdBenelux::parcelshop missing countryId');
            return $this->errorResponse($this->language->get('error_invalid_address'));
        }

        $countryInfo = $this->model_localisation_country->getCountry($this->request->post['countryId']);
        if (empty($countryInfo)) { // Country not found
            $log->write('ControllerExtensionShippingDpdBenelux::parcelshop Country not found: '. $this->request->post['countryId']);
            return $this->errorResponse($this->language->get('error_invalid_address'));
        }
        $countryCode = $countryInfo['iso_code_2'];
        $postcode = $this->request->post['postcode'];

        $client = new GoogleMapsClient($this->config->get('shipping_dpdbenelux_parcelshop_google_maps_api_server_key'), $log);
        $coordinates = $client->getGoogleMapsCenter($postcode, $countryCode);
        if (null === $coordinates) {
            return $this->errorResponse($this->language->get('error_invalid_address'));
        }

        $parcelshops = $this->retrieveParcelShops($coordinates, $countryCode);
        if (null === $parcelshops) {
            return $this->errorResponse($this->language->get('error_retrieving_parcelshops'));
        }

        $resultData = array();
        $resultData['success'] = true;
        $resultData['parcelshops'] = $parcelshops;
        $resultData['mapWidth'] = $this->config->get('shipping_dpdbenelux_parcelshop_google_maps_width');
        $resultData['mapWidthType'] = $this->config->get('shipping_dpdbenelux_parcelshop_google_maps_width_type');
        $resultData['mapHeight'] = $this->config->get('shipping_dpdbenelux_parcelshop_google_maps_height');
        $resultData['latitude'] = $coordinates[0];
        $resultData['longitude'] = $coordinates[1];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($resultData));
    }

    private function errorResponse($message)
    {
        $resultData['success'] = false;
        $resultData['error_message'] = $message;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($resultData));
    }

    /**
     *
     * @param array $coordinates
     * @return \DpdConnect\Sdk\Resources\Parcelshop|null
     */
    private function retrieveParcelShops($coordinates, $countryCode)
    {
        $query = ['longitude'  => $coordinates[1],
                  'latitude'   => $coordinates[0],
                  'countryIso' => $countryCode,
                  'consigneePickupAllowed' => true,
                  'limit' => 10,
        ];
        try {
            $shops = DpdConnectClientBuilder::buildAuthenticatedClientUsingDpdAuthentication($this->dpdauthentication)->getParcelshop()->getList($query);
            return $shops;
        } catch (\Exception $e) {
            $this->registry->get('log')->write((string) $e);
            return null;
        }
    }

    public function asyncShipmentCallback()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->library('dpd/dpdauthentication');
        $this->load->model('extension/shipping/dpd_batch');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();

        // We don't want to retrieve the body before we know the request is legitimate
        $nonce = isset($this->request->get['nonce']) ? $this->request->get['nonce'] : null;
        if ($nonce == '') {
            $this->registry->get('log')->write(
                'asyncShipmentCallback without nonce get: ' . json_encode($this->request->get)
            );
            return;
        }

        $batch = $this->model_extension_shipping_dpd_batch->findBatchByNonce($nonce);
        if (null === $batch) {
            $this->registry->get('log')->write(
                'asyncShipmentCallback no batch with nonce get: ' . json_encode($this->request->get)
            );
            return;
        }

        // Nonce found, assume request is legitimate
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        $lastError = json_last_error();
        if ($lastError != JSON_ERROR_NONE) {
            $this->registry->get('log')->write(
                'asyncShipmentCallback json_decode error: '
                . json_last_error_msg()
                . ' body: '. $body
            );
            return;
        }

        $manager = new LabelCallbackManager(
            $this->model_extension_shipping_dpd_shipment,
            $this->model_extension_shipping_dpd_batch,
            $this->registry->get('log'),
            $this->dpdauthentication
        );
        $manager->processCallback($batch, $data);



    }

}
