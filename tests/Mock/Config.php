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

class Config
{
    private $dictionary = [
        'shipping_dpdbenelux_status'                => '1',
        'shipping_dpdbenelux_username'              => 'testuser',
        'shipping_dpdbenelux_sending_depot'         => '0522',
        'shipping_dpdbenelux_account_type'          => 'B2C',
        'shipping_dpdbenelux_environment'           => '2',
        'shipping_dpdbenelux_paper_format'          => 'A4',
        'shipping_dpdbenelux_enable_scp_ncp_choice' => null,
        'shipping_dpdbenelux_include_return_label'  => '1',
        'shipping_dpdbenelux_sender_company_name'   => 'testcompany',
        'shipping_dpdbenelux_sender_street'         => 'teststreet 1',
        'shipping_dpdbenelux_sender_postal_code'    => '1111AB',
        'shipping_dpdbenelux_sender_place'          => 'teststad',
        'shipping_dpdbenelux_sender_country_code'   => 'NL',
        'shipping_dpdbenelux_sender_email'          => 'shop@example.com',
        'shipping_dpdbenelux_predict_title'        => 'Predict',
        'shipping_dpdbenelux_predict_description'  => '',
        'shipping_dpdbenelux_predict_cost'         => '4,44',
        'shipping_dpdbenelux_predict_tax_class_id' => '9',
        'shipping_dpdbenelux_predict_geo_zone_id'  => '0',
        'shipping_dpdbenelux_predict_status'       => '1',
        'shipping_dpdbenelux_predict_sort_order'   => '',
        'shipping_dpdbenelux_parcelshop_title'                  => 'parcelshop',
        'shipping_dpdbenelux_parcelshop_description'            => '',
        'shipping_dpdbenelux_parcelshop_cost'                   => '2,23',
        'shipping_dpdbenelux_parcelshop_tax_class_id'           => '9',
        'shipping_dpdbenelux_parcelshop_geo_zone_id'            => '0',
        'shipping_dpdbenelux_parcelshop_status'                 => '1',
        'shipping_dpdbenelux_parcelshop_sort_order'             => '',
        'shipping_dpdbenelux_parcelshop_google_maps_api_key'    => '',
        'shipping_dpdbenelux_parcelshop_google_maps_width'      => '',
        'shipping_dpdbenelux_parcelshop_google_maps_width_type' => 'pixels',
        'shipping_dpdbenelux_parcelshop_google_maps_height'     => '',
        'shipping_dpdbenelux_parcelshop_number_of_shops'        => '10',
        'shipping_dpdbenelux_saturday_title'          => '',
        'shipping_dpdbenelux_saturday_description'    => '',
        'shipping_dpdbenelux_saturday_cost'           => '',
        'shipping_dpdbenelux_saturday_tax_class_id'   => '0',
        'shipping_dpdbenelux_saturday_geo_zone_id'    => '0',
        'shipping_dpdbenelux_saturday_status'         => '0',
        'shipping_dpdbenelux_saturday_sort_order'     => '',
        'shipping_dpdbenelux_saturday_show_from_day'  => '',
        'shipping_dpdbenelux_saturday_show_till_day'  => '',
        'shipping_dpdbenelux_saturday_show_from_time' => '',
        'shipping_dpdbenelux_saturday_show_till_time' => '',
        'shipping_dpdbenelux_classic_saturday_title'          => '',
        'shipping_dpdbenelux_classic_saturday_description'    => '',
        'shipping_dpdbenelux_classic_saturday_cost'           => '',
        'shipping_dpdbenelux_classic_saturday_tax_class_id'   => '0',
        'shipping_dpdbenelux_classic_saturday_geo_zone_id'    => '0',
        'shipping_dpdbenelux_classic_saturday_status'         => '0',
        'shipping_dpdbenelux_classic_saturday_sort_order'     => '',
        'shipping_dpdbenelux_classic_saturday_show_from_day'  => '',
        'shipping_dpdbenelux_classic_saturday_show_till_day'  => '',
        'shipping_dpdbenelux_classic_saturday_show_from_time' => '',
        'shipping_dpdbenelux_classic_saturday_show_till_time' => '',
        'shipping_dpdbenelux_classic_title'        => 'classic',
        'shipping_dpdbenelux_classic_description'  => '',
        'shipping_dpdbenelux_classic_cost'         => '',
        'shipping_dpdbenelux_classic_tax_class_id' => '0',
        'shipping_dpdbenelux_classic_geo_zone_id'  => '0',
        'shipping_dpdbenelux_classic_status'       => '0',
        'shipping_dpdbenelux_classic_sort_order'   => '',
        'shipping_dpdbenelux_guarantee18_title'        => 'Guarantee 18:00',
        'shipping_dpdbenelux_guarantee18_description'  => '',
        'shipping_dpdbenelux_guarantee18_cost'         => '10,50',
        'shipping_dpdbenelux_guarantee18_tax_class_id' => '9',
        'shipping_dpdbenelux_guarantee18_geo_zone_id'  => '0',
        'shipping_dpdbenelux_guarantee18_status'       => '1',
        'shipping_dpdbenelux_guarantee18_sort_order'   => '',
        'shipping_dpdbenelux_express12_title'          => 'Express 12:00',
        'shipping_dpdbenelux_express12_description'    => '',
        'shipping_dpdbenelux_express12_cost'           => '32,50',
        'shipping_dpdbenelux_express12_tax_class_id'   => '9',
        'shipping_dpdbenelux_express12_geo_zone_id'    => '0',
        'shipping_dpdbenelux_express12_status'         => '1',
        'shipping_dpdbenelux_express12_sort_order'     => '',
        'shipping_dpdbenelux_express10_title'          => '',
        'shipping_dpdbenelux_express10_description'    => '',
        'shipping_dpdbenelux_express10_cost'           => '',
        'shipping_dpdbenelux_express10_tax_class_id'   => '0',
        'shipping_dpdbenelux_express10_geo_zone_id'    => '0',
        'shipping_dpdbenelux_express10_status'         => '0',
        'shipping_dpdbenelux_express10_sort_order'     => '',
        'shipping_dpdbenelux_asynchronous'             => true,
        'shipping_dpdbenelux_asynchronous_from'        => 10,
        'shipping_dpdbenelux_consignor_eori_number'    => 'abc10',
        'shipping_dpdbenelux_export_hsc_source'        => 1,
        'shipping_dpdbenelux_export_hsc_default'       => 'hsc default',
        'shipping_dpdbenelux_export_value_source'      => 2,
        'shipping_dpdbenelux_export_origin_country_source' => 3,
        'shipping_dpdbenelux_export_vat_number_source' => 1,
        'shipping_dpdbenelux_country_customs'          => [],
        'shipping_dpdbenelux_sender_phone'             => '123456',
        'shipping_dpdbenelux_weight_default'           => null,
        'shipping_dpdbenelux_export_origin_country_default' => null,
    ];

    public function get($key)
    {
        return $this->dictionary[$key];
    }

    public function set($key, $value)
    {
        $this->dictionary[$key] = $value;
    }
}
