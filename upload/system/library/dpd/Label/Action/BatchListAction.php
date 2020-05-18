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
use DpdConnect\Label\Entity\Batch;
use DpdConnect\Label\JobStateManager;

class BatchListAction extends ListAction
{
    /**
     *
     * @var string 
     */
    protected $route = 'extension/shipping/dpdbenelux/batches';
    /**
     *
     * @var \Config  
     */
    private $config;
    /**
     *
     * @var \ModelExtensionShippingDpdBatch 
     */
    private $model_batch;
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

    /**
     * BatchListAction constructor.
     *
     * @param $language
     * @param $urlGenerator
     * @param $config
     * @param $model_batch
     * @param $model_shipment
     * @param JobStateManager $jobStateManager
     */
    public function __construct($language, $urlGenerator, $config, $model_batch, $model_shipment, JobStateManager $jobStateManager)
    {
        parent::__construct($language, $urlGenerator);
        $this->config = $config;
        $this->model_batch = $model_batch;
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
        $sortOptions = ['id' => 'b.id', 'started' => 'b.started', 'orders' => 'min_order_id'];
        $filterOptions = ['id', 'started', 'order_id' , 'status'];

        $page = isset($getData['page'])
            ? $getData['page'] : 1;
        $sort = isset($getData['sort']) && isset($sortOptions[$getData['sort']])
            ? $getData['sort'] : 'id';
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
            'status_colors' => [
                Batch::StatusRequest => '#e79520',
                Batch::StatusQueued => '#e79520',
                Batch::StatusProcessing => '#e79520',
                Batch::StatusSuccess => 'rgb(60, 147, 60)',
                Batch::StatusFailed => 'rgb(227, 80, 62)',
                Batch::StatusPartiallyFailed => '#e79520',
            ],
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
        $data['breadcrumbs'][] = [   'text' => $this->language->get('menu_option_batches'),
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
        $total_count = $this->model_batch->countBatches($filters);

        $data['batches'] = $this->setShipments(
            $this->model_batch->findBatches($filters, $sortOptions[$sort], $order, $limit, $start)
        );

        $data['pagination'] = $this->getPagination($total_count, $page, $limit, $args);
        $data['results'] = $this->getPaginationText($total_count, $start, $limit);

        return $data;
    }

    /**
     * Retrieve the shipments with batch_id of each Batch and update their job states if needed
     *
     * @param  array $batches of Batch
     * @return batches
     */
    private function setShipments($batches)
    {
        foreach ($batches as $batch) {
            $shipments = [];
            foreach ($this->model_shipment->getShipmentsWithBatchId($batch->getId()) as $shipment) {
                $shipments[] = $this->jobStateManager->updateJobState($shipment, $batch);
            }

            $batch->setShipments($shipments);
        }
        return $batches;
    }

}
