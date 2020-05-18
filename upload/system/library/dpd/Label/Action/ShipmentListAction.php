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

namespace DpdConnect\Label\Action;

use DpdConnect\Common\ListAction;
use DpdConnect\Label\JobStateManager;


class ShipmentListAction extends ListAction
{
    /**
     *
     * @var string 
     */
    protected $route = 'extension/shipping/dpdbenelux/shipments';
    /**
     *
     * @var \Config  
     */
    private $config;
    /**
     *
     * @var \ModelExtensionShippingDpdShipment 
     */
    private $model_shipment;
    /**
     *
     * @var JobStateManager 
     */
    private $jobStateManager;

    public function __construct($language, $urlGenerator, $config, $model_shipment, JobStateManager $jobStateManager)
    {
        parent::__construct($language, $urlGenerator);
        $this->config = $config;
        $this->model_shipment = $model_shipment;
        $this->jobStateManager = $jobStateManager;
    }

    /**
     * Perform the action
     *
     * @param  array $getData from get request
     * @return array View data
     */
    public function perform($getData)
    {
        $sortOptions = ['batch_id' => 's.batch_id', 'created' => 's.date_added', 'order_id' => 's.order_id', 'is_return' => 's.is_return'];
        $filterOptions = ['batch_id', 'created', 'order_id', 'is_return', 'status'];

        $page = isset($getData['page'])
            ? $getData['page'] : 1;
        $sort = isset($getData['sort']) && isset($sortOptions[$getData['sort']])
            ? $getData['sort'] : 'order_id';
        $order = isset($getData['order']) && $getData['order'] == 'ASC'
            ? 'ASC' : 'DESC';

        $args = [
            'sort' => $sort,
            'order' => $order,
        ];
        $data = [
            'dpd_language' => $this->language,
            'breadcrumbs' => [
                [   'text' => $this->language->get('text_home'),
                    'href' => $this->urlGenerator->link('common/dashboard', true) ],
            ],
            'status_colors' => $this->getStatusColors(),
            'url_generator' => $this->urlGenerator,
            'filter_url' => str_replace('&amp;', '&', $this->urlGenerator->link($this->route, $args, true)), // must be without filters and page
        ];

        $args['page'] = $page;
        $filters = [];
        foreach ($filterOptions as $option) {
            if (isset($getData['filter'][$option])) {
                $filters[$option] = $getData['filter'][$option];
            }
        }

        $args['filter'] = $filters;
        $data['filter'] = $filters;

        if (isset($filters['batch_id'])) {
            $data['breadcrumbs'][] = [   'text' => $this->language->get('menu_option_batches'),
                                         'href' =>  $this->urlGenerator->link('extension/shipping/dpdbenelux/batches', '', true)];
        }
        $data['breadcrumbs'][] = [   'text' => $this->language->get('shipments_heading_title'),
                                     'href' =>  $this->urlGenerator->link($this->route, $args, true)];

        $sortUrls = [];
        foreach ($sortOptions as $option => $column) {
            $sortArgs = $args;
            if ($sort == $option) {
                $sortArgs['order'] =  $order == 'ASC' ? 'DESC' : 'ASC';
            } else {
                $sortArgs['sort'] = $option;
            }
            $sortUrls[$option] = $this->urlGenerator->link($this->route, $sortArgs, true);
        }
        $data['sort_url'] = $sortUrls;


        $limit = $this->config->get('config_limit_admin');
        $start = ($page - 1) * $limit;
        $total_count = $this->model_shipment->countShipments($filters);

        $data['shipments'] = $this->enrich(
            $this->model_shipment->findShipments($filters, $sortOptions[$sort], $order, $limit, $start)
        );

        $data['pagination'] = $this->getPagination($total_count, $page, $limit, $args);
        $data['results'] = $this->getPaginationText($total_count, $start, $limit);

        return $data;
    }

    public static function getStatusColors()
    {
        return [
            \ModelExtensionShippingDpdShipment::StatusRequest => 'rgb(243, 166, 56)',
            \ModelExtensionShippingDpdShipment::StatusQueued => 'rgb(243, 166, 56)',
            \ModelExtensionShippingDpdShipment::StatusProcessing => 'rgb(243, 166, 56)',
            \ModelExtensionShippingDpdShipment::StatusSuccess => 'rgb(60, 147, 60)',
            \ModelExtensionShippingDpdShipment::StatusFailed => 'rgb(227, 80, 62)',
        ];
    }

    /**
     *
     * @param  array $shipments
     * @return array shipments with data added that is needed by the view
     */
    private function enrich($shipments)
    {
        $result = [];
        foreach ($shipments as $shipment) {
            $shipment = $this->jobStateManager->updateJobState($shipment);
            $label_numbers = null;
            if (!empty($shipment['label_numbers'])) {
                $label_numbers = unserialize($shipment['label_numbers']);
            }
            if (is_array($label_numbers)) {
                $parcelNumbers = [];
                foreach ($label_numbers as $parcelData) {
                    $parcelNumbers[] = $parcelData['parcel_number']. ' ';
                }
                $shipment['parcelNumbers'] = $parcelNumbers;
            }
            $shipment['status'] = $this->model_shipment->getStatus($shipment);
            $shipment['errorText'] = $this->model_shipment->getErrorText($shipment);
            $result[] = $shipment;
        }

        return $result;
    }
}
