<?php
/**
 * This file is part of the OpenCart Shipping module of DPD Nederland B.V.
 *
 * Copyright (C) 2018 DPD Nederland B.V.
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

use DPD\DpdConfiguration;
use DpdConnect\Settings\IndexSetAction;
use DpdConnect\Common\UrlGenerator;
use DpdConnect\Settings\IndexGetAction;
use DpdConnect\Label\LabelGenerationManager;
use DpdConnect\Label\LabelCallbackManager;
use DpdConnect\Label\Action\BatchListAction;
use DpdConnect\Label\Action\ShipmentListAction;
use DpdConnect\Label\JobStateManager;
use DpdConnect\Label\LabelRequestFactory;

use Mpdf\Mpdf;

class ControllerExtensionShippingDpdBenelux extends Controller
{

    public $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function install()
    {
        $this->load->model('extension/shipping/dpd_shipment');

        $this->model_extension_shipping_dpd_shipment->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/shipping/dpd_shipment');

        $this->model_extension_shipping_dpd_shipment->uninstall();
    }

    /**
     * Settings endpoint
     */
    public function index()
    {

        $this->load->language('extension/shipping/dpdbenelux');
        $this->document->setTitle($this->language->get('heading_title'));

        // This also triggers the load of other DPD components
        $this->load->library('dpd/dpdauthentication');

        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();
        $urlGenerator = new UrlGenerator($this->url, $this->session->data['user_token']);

        //catch the post request
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $this->load->model('setting/setting');
            $setAction = new IndexSetAction(
                $this->language,
                $this->user,
                $this->config,
                $this->model_setting_setting,
                $this->registry->get('dpdauthentication'),
                $this->encryption
            );

            $this->error = array_merge($this->error, $setAction->perform($this->request->post));

            if (empty($this->error)) {
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($urlGenerator->link('marketplace/extension', '&type=shipping'));
            }
        }

        $this->load->model('localisation/tax_class');
        $this->load->model('localisation/geo_zone');
        $this->load->model('localisation/country');
        $this->load->model('catalog/attribute');
        $this->load->model('customer/custom_field');
        $getAction = new IndexGetAction(
            $this->language,
            $this->config,
            $urlGenerator,
            $this->model_localisation_tax_class,
            $this->model_localisation_geo_zone,
            $this->model_localisation_country,
            $this->model_catalog_attribute,
            $this->model_customer_custom_field
        );

        $data = $getAction->perform($this->request->post, $this->error);

        // Load OpenCart templates
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/dpdbenelux', $data));
    }

    public function batches()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('extension/shipping/dpd_batch');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();

        $jobStateManager = new JobStateManager(
            $this->model_extension_shipping_dpd_shipment,
            $this->model_extension_shipping_dpd_batch,
            $this->registry->get('log'),
            $this->dpdauthentication
        );
        $action = new BatchListAction(
            $this->language,
            new UrlGenerator($this->url, $this->session->data['user_token']),
            $this->config,
            $this->model_extension_shipping_dpd_batch,
            $this->model_extension_shipping_dpd_shipment,
            $jobStateManager
        );
        $data = $action->perform($this->request->get);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/shipping/dpdbatchlist', $data));
    }

    public function shipments()
    {
        $this->document->addScript('view/javascript/dpd/dpd_shipment_list.js');

        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->model('extension/shipping/dpd_batch');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();

        $jobStateManager = new JobStateManager(
            $this->model_extension_shipping_dpd_shipment,
            $this->model_extension_shipping_dpd_batch,
            $this->registry->get('log'),
            $this->dpdauthentication
        );
        $action = new ShipmentListAction(
            $this->language,
            new UrlGenerator($this->url, $this->session->data['user_token']),
            $this->config,
            $this->model_extension_shipping_dpd_shipment,
            $jobStateManager
        );
        $data = $action->perform($this->request->get);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/dpdshipmentlist', $data));
    }

    public function editProductData()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('extension/shipping/dpd_product_extension');
        if (isset($this->request->post['product_id'])) {
            // Formatting
            $data = $this->request->post;
            $data['export_origin_country'] = isset($data['export_origin_country'])
                ? strtoupper($data['export_origin_country']) : null;

            $errors = $this->model_extension_shipping_dpd_product_extension->validate($data);
            if (empty($errors)) {
                $data = $this->model_extension_shipping_dpd_product_extension->save($data);
            } else {
                foreach ($errors as $field => $error) {
                    $data['errors'][$field] = $this->language->get($error);
                }

            }
        } else {
            $productId = $this->request->get['product_id'];
            $data = $this->model_extension_shipping_dpd_product_extension->find($productId);
            $data['product_id'] = $productId;
        }

        $this->response->setOutput($this->load->view('extension/shipping/dpdproductdata', $data));
    }

    public function generateShippinglist()
    {
        $this->load->language('extension/shipping/dpdbenelux');

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']));
            exit;
        }

        if(!isset($this->request->post['selected']) || count($this->request->post['selected']) == 0) {
            $this->session->data['warning'] = $this->language->get('error_no_orders_selected');
            $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']));
            exit;
        }

        $this->load->model('sale/order');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->library('dpd/dpdconfiguration');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();
        $factory = new LabelRequestFactory($this->config, \DpdConnect\Sdk\DpdConnectClientBuilder::buildAuthenticatedClientUsingDpdAuthentication($this->dpdauthentication));

        $selectedOrderIds = $this->request->post['selected'];
        
        $data = array();
        $data['logo_path'] = 'view/image/shipping/dpd.png';
        $includeCustoms = false;

        try {

            foreach ($selectedOrderIds as $orderId) {

                $order = $this->model_sale_order->getOrder($orderId);

                // Ignore all orders that don't start with dpdbenelux as their shipping code
                if (strpos($order['shipping_code'], 'dpdbenelux') !== 0) {
                    continue;
                }

                $dpdShipment = $this->model_extension_shipping_dpd_shipment->getShipment($order['order_id'], false);
                if (count($dpdShipment) == 0) {
                    continue;
                }

                $recipient = $factory->getRecipientAddress($order);
                $includeCustoms = $includeCustoms && $factory->includeCustoms($recipient);
                $carrierName = $factory->getShippingListCarrierCode($order['shipping_code']);

                $parcelData = unserialize($dpdShipment['label_numbers']);
                // $parcels = $parcelData[0]['parcel_number'];

                foreach ($parcelData as $parcel) {
                    $data['list'][] = [
                        'parcelLabelNumber' => $parcel['parcel_number'],
                        'weight'            => round($parcel['weight'] * 10, 2) . ' g',
                        'carrierName'       => $carrierName,
                        'customerName'      => $recipient['name1'],
                        'address'           => $recipient['street'],
                        'zipCode'           => $recipient['postalcode'],
                        'city'              => $recipient['city'],
                        'referenceNumber'   => $order['order_id'],
                    ];
                }
            }
            $data['sender'] = $factory->getSenderAddress($includeCustoms);;
        } catch (\DpdConnect\Sdk\Exceptions\DpdException $e) {
            $this->response->setOutput(strlen($e->getMessage()) == 0 ? get_class($e) : $e->getMessage());
            return;
        }
        $this->response->setOutput($this->load->view('extension/shipping/dpdshippinglist', $data));
    }

    public function generateLabel()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->model('extension/shipping/dpd_batch');
        $this->load->model('extension/shipping/dpd_product_extension');
        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        $this->load->model('localisation/weight_class');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();
        $urlGenerator = new UrlGenerator($this->url, $this->session->data['user_token']);

        if ($this->request->server['REQUEST_METHOD'] != 'POST' && !isset($this->request->get['order_id'])) {
            $this->session->data['warning'] = $this->language->get('error_no_orders_selected');
            $this->response->redirect($urlGenerator->link('sale/order'));
            return;
        }

        if((!isset($this->request->post['selected']) || count($this->request->post['selected']) == 0) && !isset($this->request->get['order_id'])) {
            $this->session->data['warning'] = $this->language->get('error_no_orders_selected');
            $this->response->redirect($urlGenerator->link('sale/order'));
            return;
        }

        $returnLabelOnly = false;
        if(isset($this->request->get['return-only'])) {
            $returnLabelOnly = $this->request->get['return-only'];
        }

        $parcelCount = 1;
        if(isset($this->request->get['parcels'])) {
            $parcelCount = $this->request->get['parcels'];
        }

        if($this->request->server['REQUEST_METHOD'] == 'POST') {
            $selectedOrderIds = $this->request->post['selected'];
        } else {
            $selectedOrderIds = [$this->request->get['order_id']];
        }

        $includeReturnLabel = $this->config->get('shipping_dpdbenelux_include_return_label');

        $genMan = new LabelGenerationManager(
            $this->model_sale_order,
            $this->model_catalog_product,
            $this->model_localisation_weight_class,
            $this->model_extension_shipping_dpd_shipment,
            $this->model_extension_shipping_dpd_batch,
            $this->model_extension_shipping_dpd_product_extension,
            $this->dpdauthentication,
            $this->registry->get('log'),
            $this->config,
            $this->language
        );
        $batch = $genMan->generateLabels($selectedOrderIds, $parcelCount, $includeReturnLabel, $returnLabelOnly, true);

        if (count($genMan->getErrors()) > 0) {
            $this->reportGenerationErrors($genMan->getErrors(), $urlGenerator);
        } else {
            $this->session->data['success'] = $this->language->get(null === $batch->getNonce() ? 'text_labels_generated' : 'text_labels_queued')
            .' '. count($batch->getShipments()). ' '. $this->language->get('text_shipments');
        }

        $this->response->redirect(
            isset($this->request->get['order_id'])
                ? $urlGenerator->link('sale/order/info', ['order_id' => $this->request->get['order_id']], true)
                : $urlGenerator->link('sale/order', '', true)
        );
        exit;
    }


    public function downloadLabel()
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->model('extension/shipping/dpd_shipment');
        $this->load->model('extension/shipping/dpd_batch');
        $this->load->model('sale/order');
        $this->load->library('dpd/dpdauthentication');
        $this->load->library('dpd/dpd_autoload');
        $this->registry->get('dpd_autoload')->start();
        $urlGenerator = new UrlGenerator($this->url, $this->session->data['user_token']);
        $manager = new LabelCallbackManager(
            $this->model_extension_shipping_dpd_shipment,
            $this->model_extension_shipping_dpd_batch,
            $this->registry->get('log'),
            $this->dpdauthentication
        );

        if (isset($this->request->get['shipment_id'])) {
            $shipments = $this->model_extension_shipping_dpd_shipment->findShipments(
                ['dpd_shipment_id' => $this->request->get['shipment_id']]
            );
        } elseif (isset($this->request->get['batch_id'])) {
            $batch = $this->model_extension_shipping_dpd_batch->findBatch($this->request->get['batch_id'], true);
            if (null === $batch) {
                $this->session->data['warning'] = 'Batch not found: '. $this->request->get['batch_id'];
                $this->response->redirect($urlGenerator->link('sale/order'));
                return;
            }
            $returnLabelOnly = false;
            $shipments = $this->model_extension_shipping_dpd_shipment->getShipmentsWithBatchId($batch->getId());
        } else {
            $selectedOrderIds = $this->request->server['REQUEST_METHOD'] == 'POST'
            ? $this->request->post['selected']
            : [$this->request->get['order_id']];
            $returnLabelOnly = isset($this->request->get['return-only'])
            ? $this->request->get['return-only']
            : false;

            $shipments = $this->getOrderShipments($selectedOrderIds, $returnLabelOnly);
        }

        $pdfFiles = [];
        foreach($shipments as $dpdShipment) {
            if (empty($dpdShipment['mps_id'])) {
                continue;
            }
            $prefix = $dpdShipment['is_return'] ? 'dpd-return-label-order-' : 'dpd-label-order-';
            $pdfFiles[$prefix . $dpdShipment['order_id'] . '.pdf'] = isset($dpdShipment['label'])
                ? $dpdShipment['label']
                : $manager->retrieveAndStoreLabel($dpdShipment);
        }

        if(count($pdfFiles) === 0) {
            $this->session->data['warning'] = $this->language->get('error_no_labels');
            $this->response->redirect($urlGenerator->link('sale/order'));
            return;
        }


        // With only one PDF file we can just output it as download directly
        if(count($pdfFiles) === 1) {
            $content = reset($pdfFiles);
            $filename = key($pdfFiles);
            header("Content-type:application/pdf");
            header('Content-disposition: attachment; filename='.$filename);
            header('Content-Length: ' . strlen($content));
            // non standard
            header("Content-Transfer-Encoding: Binary");
            header("Content-Description: File Transfer");
            echo $content;
            die;
        }

        $zipname = sprintf('dpd-labels-%s.zip', date('Ymd-His'));

        $zip = new ZipArchive;
        $zip->open(DIR_DOWNLOAD . $zipname, ZipArchive::CREATE);

        foreach($pdfFiles as $fileName => $content) {
            $zip->addFromString($fileName, $content);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize(DIR_DOWNLOAD . $zipname));
        // non standard
        header("Content-Transfer-Encoding: Binary");
        header("Content-Description: File Transfer");

        readfile(DIR_DOWNLOAD . $zipname);

        unlink(DIR_DOWNLOAD . $zipname);
        die;
    }

    private function getOrderShipments($selectedOrderIds, $returnLabelOnly)
    {
        $includeReturnLabel = $this->config->get('shipping_dpdbenelux_include_return_label');

        $shipments = [];
        foreach($selectedOrderIds as $orderId) {
            $order = $this->model_sale_order->getOrder($orderId);

            // Ignore all orders that don't start with dpdbenelux as their shipping code
            if(strpos($order['shipping_code'], 'dpdbenelux') !== 0) {
                continue;
            }

            if(!$returnLabelOnly) {
                $shipments[] = $this->model_extension_shipping_dpd_shipment->getShipment($order['order_id'], false);
            }
            if($includeReturnLabel && !isset($this->request->get['return-only']) || $returnLabelOnly) {
                $shipments[] = $this->model_extension_shipping_dpd_shipment->getShipment($order['order_id'], true);
            }
        }
        return $shipments;
    }

    private function reportGenerationErrors($errors, $urlGenerator)
    {
        $errorMessage = $this->language->get('error_generating_labels'). '<ul>';
        foreach ($errors as $error) {
            $errorMessage .= '<li>';
            if (isset($error['order_id'])) {
                $errorMessage .= 'Order '. $error['order_id']. ': ';
            }
            if (isset($error['isReturnLabel']) && $error['isReturnLabel']) {
                $errorMessage .= 'Returnlabel ';
            }
            if (isset($error['metaDataPath'])) {
                $errorMessage .= $error['metaDataPath']. ' ';
            }
            if (isset($error['dataPath'])) {
                $errorMessage .= $error['dataPath']. ' ';
            }
            $errorMessage .= $error['message'];
            if (isset($error['logref'])) {
                $errorMessage .= ' logref: '. $error['logref'];
            }
            $errorMessage .= '</li>';
        }
        $errorMessage .= '</ul>';
        $this->session->data['warning'] = $errorMessage;
    }



}
