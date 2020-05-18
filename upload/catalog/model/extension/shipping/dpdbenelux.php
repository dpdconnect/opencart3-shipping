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

class ModelExtensionShippingDpdBenelux extends Model
{
    function getQuote($address)
    {
        $this->load->language('extension/shipping/dpdbenelux');
        $this->load->library('dpd/dpdconfiguration');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_dpdpredict_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('shipping_dpdpredict_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            $moduleName = 'shipping_dpdbenelux';

            $accountType = $this->config->get($moduleName . '_account_type');

            foreach(\DPD\DpdConfiguration::carrierNames as $carrierKey => $carrierData)
            {
                // Check for B2C and B2B
                if($accountType !== $carrierData['type']) {
                    continue;
                }

                // Check if DPD method is enabled
                $status = $this->config->get($moduleName . '_' . $carrierKey . '_status');
                if(!$status) {
                    continue;
                }

                if($carrierKey == 'saturday' || $carrierKey == 'classic_saturday') {
                    if (!DPD\DpdConfiguration::isSaturdayAllowed($carrierKey)) {
                        continue;
                    }
                }

                $configKey = $moduleName . '_' . $carrierKey . '_';

                $quote_data[$carrierKey] = array(
                 'code'         => 'dpdbenelux.' . $carrierKey,
                 'title'        => '<strong>' . $this->config->get($configKey . 'title') . '</strong> - ' . $this->config->get($configKey . 'description'),
                 'cost'         => $this->config->get($configKey . 'cost'),
                 'tax_class_id' => $this->config->get($configKey . 'class_id'),
                 'text'         => $this->currency->format($this->tax->calculate((int)$this->config->get($configKey . 'cost'), $this->config->get($configKey . 'tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }


            $method_data = array(
            'code'       => 'dpdbenelux',
            'title'      => 'DPD Parcelservice',//$this->config->get('shipping_dpdpredict_title'),
            'quote'      => $quote_data,
            'sort_order' => 0,//$this->config->get('shipping_dpdpredict_sort_order'),
            'error'      => false
            );
        }

        return $method_data;
    }
}