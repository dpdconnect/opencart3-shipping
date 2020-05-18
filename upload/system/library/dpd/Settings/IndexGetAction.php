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

namespace DpdConnect\Settings;


use Cart\User;
use DPD\DpdConfiguration;
use DpdConnect\Common\UrlGenerator;

class IndexGetAction
{
    const MODULE_NAME = 'shipping_dpdbenelux';
    const PARAMS_BASE = [
        'status', 'url', 'username', 'sending_depot', 'account_type', 'environment', 'paper_format','enable_scp_ncp_choice',
        'include_return_label', 'asynchronous', 'asynchronous_from', 'weight_default',
        'sender_company_name', 'sender_street', 'sender_postal_code', 'sender_place', 'sender_country_code',
        'sender_phone', 'consignor_eori_number',
        'export_hsc_source', 'export_hsc_default', 'export_value_source', 'export_origin_country_source',
        'export_origin_country_default', 'export_vat_number_source',
        ];
    const PARAMS_CARRIER = ['title', 'description', 'cost', 'tax_class_id', 'geo_zone_id', 'status', 'sort_order'];
    const PARAMS_CARRIER_PARCELSHOP = ['google_maps_api_client_key', 'google_maps_api_server_key', 'google_maps_width', 'google_maps_width_type', 'google_maps_height', 'number_of_shops'];
    const PARAMS_CARRIERS_SATERDAY = ['show_from_day', 'show_till_day', 'show_from_time', 'show_till_time'];
    const PARAMS_DEFAULTS = [
        'asynchronous' => 1,
        'asynchronous_from' => 10
    ];

    /**
     *
     * @var \Language  
     */
    private $language;
    /**
     *
     * @var \Config  
     */
    private $config;
    /**
     *
     * @var UrlGenerator  
     */
    private $urlGenerator;
    /**
     *
     * @var \ModelLocalisationTaxClass  
     */
    private $model_localisation_tax_class;
    /**
     *
     * @var \ModelLocalisationGeoZone  
     */
    private $model_localisation_geo_zone;
    /**
     *
     * @var \ModelLocalisationCountry 
     */
    private $model_localisation_country;
    /**
     *
     * @var \ModelCatalogAttribute 
     */
    private $model_catalog_attribute;
    /**
     *
     * @var \ModelCustomerCustomField 
     */
    private $model_custom_field;

    /**
     * IndexGetAction constructor.
     *
     * @param \Language                  $language
     * @param \Config                    $config
     * @param \ModelLocalisationTaxClass $model_localisation_tax_class
     * @param \ModelLocalisationGeoZone  $model_localisation_geo_zone
     */
    public function __construct(
        \Language $language,
        \Config $config,
        UrlGenerator $urlGenerator,
        $model_localisation_tax_class, // Proxy
        $model_localisation_geo_zone, // Proxy
        $model_localisation_country,
        $model_catalog_attribute,
        $model_custom_field
    ) {
        $this->language = $language;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->model_localisation_tax_class = $model_localisation_tax_class;
        $this->model_localisation_geo_zone = $model_localisation_geo_zone;
        $this->model_localisation_country = $model_localisation_country;
        $this->model_catalog_attribute = $model_catalog_attribute;
        $this->model_custom_field = $model_custom_field;
    }

    public function perform($postData, $errors)
    {
        $data = array();
        //set errors
        $data['errors'] = $errors;

        $data['breadcrumbs'] = array();

        //create the breadcrums
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->urlGenerator->link('common/dashboard', '', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->urlGenerator->link('marketplace/extension', '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->urlGenerator->link('extension/shipping/dpdbenelux', '', true)
        );

        $data['action'] = $this->urlGenerator->link('extension/shipping/dpdbenelux', '', true);

        $data['cancel_link'] = $this->urlGenerator->link('marketplace/extension', '&type=module', true);


        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $data['countries'] = $this->model_localisation_country->getCountries();
        $data['attribute_sources'] = $this->getAttributeSourceOptions();
        $data['custom_fields'] = $this->model_custom_field->getCustomFields();

        $data['carrier_names'] = DpdConfiguration::carrierNames;
        $data['days'] = [
            '' => $this->language->get('entry_select_day'),
            'monday' => $this->language->get('entry_days_monday'),
            'tuesday' => $this->language->get('entry_days_tuesday'),
            'wednesday' => $this->language->get('entry_days_wednesday'),
            'thursday' => $this->language->get('entry_days_thursday'),
            'friday' => $this->language->get('entry_days_friday'),
            'saturday' => $this->language->get('entry_days_saturday'),
            'sunday' => $this->language->get('entry_days_sunday'),
        ];
        $data['timezone'] = date_default_timezone_get();

        // This is the data shown in the general tab
        foreach (self::PARAMS_BASE as $param) {
            $data[self::MODULE_NAME . '_'. $param] = $this->getData($param, $postData);
        }

        $data = $this->addDefaults($data);

         // This is the data shown in all other tabs
        $this->addCarrierData($data, $postData);

        return $data;
    }

    /**
     * Add default values from PARAMS_DEFAULTS
     *
     * @param  array $data
     * @return array with defaults added
     */
    private function addDefaults($data)
    {
        foreach (self::PARAMS_DEFAULTS as $key => $value) {
            if (!isset($data[self::MODULE_NAME . '_'. $key])) {
                $data[self::MODULE_NAME . '_'. $key] = $value;
            }
        }
        return $data;
    }

    /**
     *
     * @return array with source options with attributes
     */
    private function getAttributeSourceOptions()
    {
        $sources[''] = $this->language->get('use_dpd_product_data');

        foreach ($this->model_catalog_attribute->getAttributes() as $attribute) {
            $sources[$attribute['attribute_id']] = $attribute['name'];
        }

        return $sources;
    }

    /**
     *
     * @param array      $data     to add to
     * @param array|null $postData
     */
    private function addCarrierData(array &$data, $postData)
    {
        foreach (DpdConfiguration::carrierNames as $carrierName => $carrierData) {
            foreach (self::PARAMS_CARRIER as $param) {
                $data[self::MODULE_NAME][$carrierName][$param] = $this->getData($carrierName . '_'. $param, $postData);
            }

            if ($carrierName == 'parcelshop') {
                foreach (self::PARAMS_CARRIER_PARCELSHOP as $param) {
                    $data[self::MODULE_NAME][$carrierName][$param] = $this->getData($carrierName . '_'. $param, $postData);
                }
            }

            if ($carrierName == 'saturday' || $carrierName == 'classic_saturday') {
                foreach (self::PARAMS_CARRIERS_SATERDAY as $param) {
                    $data[self::MODULE_NAME][$carrierName][$param] = $this->getData($carrierName . '_'. $param, $postData);
                }
            }
        }
    }

    /**
     *
     * @param  string     $key
     * @param  array|null $postData
     * @return mixed
     */
    private function getData($key, $postData)
    {
        if (isset($postData[self::MODULE_NAME . '_' . $key])) {
            return $postData[self::MODULE_NAME . '_' . $key];
        } else {
            return $this->config->get(self::MODULE_NAME . '_' . $key);
        }
    }
}
