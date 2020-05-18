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

namespace DpdConnect\Tests\Settings;


use dpd\dpd_autoload;
use DpdConnect\Common\UrlGenerator;
use DpdConnect\Settings\IndexGetAction;
use PHPUnit\Framework\TestCase;

class IndexGetActionTest extends TestCase
{
    private $language;
    private $subject;
    private $config;
    private $urlGenerator;
    private $model_localisation_tax_class;
    private $model_localisation_geo_zone;
    private $model_localisation_country;
    /** @var \ModelCatalogAttribute */
    private $model_catalog_attribute;
    /** @var \ModelCustomerCustomField */
    private $model_custom_field;

    protected function setUp()
    {
        require_once './upload/system/library/dpd/dpd_autoload.php';
        $autoload = new dpd_autoload();
        $autoload->start();
        require_once './upload/system/library/dpd/dpdconfiguration.php';
        require_once 'tests/Mock/Language.php';
        require_once 'tests/Mock/Config.php';
        require_once 'tests/Mock/Url.php';
        require_once 'tests/Mock/ModelLocalisationTaxClass.php';
        require_once 'tests/Mock/ModelLocalisationGeoZone.php';
        require_once 'tests/Mock/ModelLocalisationCountry.php';
        require_once 'tests/Mock/ModelCatalogAttribute.php';
        require_once 'tests/Mock/ModelCustomerCustomField.php';
        $this->language = new \Language();
        $this->config = new \Config();
        $this->urlGenerator = new UrlGenerator(new \Url(), 'srTimnUoANzKN4qjiLmoFGO4J6Fkousj');
        $this->model_localisation_tax_class = new \ModelLocalisationTaxClass();
        $this->model_localisation_geo_zone = new \ModelLocalisationGeoZone();
        $this->model_localisation_country = new \ModelLocalisationCountry();
        $this->model_catalog_attribute = new \ModelCatalogAttribute();
        $this->model_custom_field = new \ModelCustomerCustomField();

        $this->subject = new IndexGetAction(
            $this->language,
            $this->config,
            $this->urlGenerator,
            $this->model_localisation_tax_class,
            $this->model_localisation_geo_zone,
            $this->model_localisation_country,
            $this->model_catalog_attribute,
            $this->model_custom_field
        );
    }

    public function testPerform()
    {
        $result = $this->subject->perform([], []);
        self::assertEquals($this->getRef(), $result);
    }

    private function getRef()
    {
        return array(
            'errors'                                    =>
                array(),
            'breadcrumbs'                               =>
                array(
                    0 =>
                        array(
                            'text' => 'Home',
                            'href' => 'http://opencart.test:8888/admin/index.php?route=common/dashboard&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
                        ),
                    1 =>
                        array(
                            'text' => 'Extensions',
                            'href' => 'http://opencart.test:8888/admin/index.php?route=marketplace/extension&amp;type=module&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
                        ),
                    2 =>
                        array(
                            'text' => 'DPD Parcelservice',
                            'href' => 'http://opencart.test:8888/admin/index.php?route=extension/shipping/dpdbenelux&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
                        ),
                ),
            'action'                                    => 'http://opencart.test:8888/admin/index.php?route=extension/shipping/dpdbenelux&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
            'cancel_link'                               => 'http://opencart.test:8888/admin/index.php?route=marketplace/extension&amp;type=module&amp;user_token=srTimnUoANzKN4qjiLmoFGO4J6Fkousj',
            'tax_classes'                               =>
                array(
                    0 =>
                        array(
                            'tax_class_id'  => '9',
                            'title'         => 'Taxable Goods',
                            'description'   => 'Taxed goods',
                            'date_added'    => '2009-01-06 23:21:53',
                            'date_modified' => '2019-04-01 13:04:25',
                        ),
                    1 =>
                        array(
                            'tax_class_id'  => '10',
                            'title'         => 'Downloadable Products',
                            'description'   => 'Downloadable',
                            'date_added'    => '2011-09-21 22:19:39',
                            'date_modified' => '2019-04-01 13:05:08',
                        ),
                ),
            'geo_zones'                                 =>
                array(
                    0 =>
                        array(
                            'geo_zone_id'   => '5',
                            'name'          => 'NL VAT ZONE',
                            'description'   => 'VAT Zone for the Netherlands',
                            'date_added'    => '2019-04-01 12:55:02',
                            'date_modified' => '0000-00-00 00:00:00',
                        ),
                    1 =>
                        array(
                            'geo_zone_id'   => '4',
                            'name'          => 'UK Shipping',
                            'description'   => 'UK Shipping Zones',
                            'date_added'    => '2009-06-23 01:14:53',
                            'date_modified' => '2010-12-15 15:18:13',
                        ),
                    2 =>
                        array(
                            'geo_zone_id'   => '3',
                            'name'          => 'UK VAT Zone',
                            'description'   => 'UK VAT',
                            'date_added'    => '2009-01-06 23:26:25',
                            'date_modified' => '2010-02-26 22:33:24',
                        ),
                ),
            'countries' =>
                array(
                    array('country_id' => '21','name' => 'Belgium','iso_code_2' => 'BE','iso_code_3' => 'BEL','address_format' => '{firstname} {lastname}
{company}
{address_1}
{address_2}
{postcode} {city}
{country}','postcode_required' => '0','status' => '1'),
                    array('country_id' => '124','name' => 'Luxembourg','iso_code_2' => 'LU','iso_code_3' => 'LUX','address_format' => '','postcode_required' => '0','status' => '1'),
                    array('country_id' => '150','name' => 'Netherlands','iso_code_2' => 'NL','iso_code_3' => 'NLD','address_format' => '','postcode_required' => '0','status' => '1')
                ),
            'attribute_sources' => [
                '' => 'Use DPD Shipping Data',
                1 => 'HSC',
                2 => 'Customs Value',
                3 => 'Origin Country Code',
            ],
            'custom_fields' => [
                ['custom_field_id' => 1, 'name' => 'Consignee VAT Number'],
            ],
            'carrier_names'                             =>
                array(
                    'predict'          =>
                        array(
                            'name'        => 'DPD Predict',
                            'description' => 'DPD Predict delivery',
                            'type'        => 'B2C',
                        ),
                    'parcelshop'       =>
                        array(
                            'name'        => 'DPD Parcelshop',
                            'description' => 'DPD Parcelshop delivery',
                            'type'        => 'B2C',
                        ),
                    'saturday'         =>
                        array(
                            'name'        => 'DPD Saturday',
                            'description' => 'DPD Saturday delivery',
                            'type'        => 'B2C',
                        ),
                    'classic_saturday' =>
                        array(
                            'name'        => 'DPD Classic Saturday',
                            'description' => 'DPD Saturday delivery',
                            'type'        => 'B2B',
                        ),
                    'classic'          =>
                        array(
                            'name'        => 'DPD Classic',
                            'description' => 'DPD Classic delivery',
                            'type'        => 'B2B',
                        ),
                    'guarantee18'      =>
                        array(
                            'name'        => 'Guarantee 18:00',
                            'description' => 'DPD Guarantee 18:00 delivery',
                            'type'        => 'B2B',
                        ),
                    'express12'        =>
                        array(
                            'name'        => 'Express 12:00',
                            'description' => 'DPD Express 12:00 delivery',
                            'type'        => 'B2B',
                        ),
                    'express10'        =>
                        array(
                            'name'        => 'Express 10:00',
                            'description' => 'DPD Express 10:00 delivery',
                            'type'        => 'B2B',
                        ),
                ),
            'days'                                      =>
                array(
                    ''          => 'Select a day',
                    'monday'    => 'Monday',
                    'tuesday'   => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday'  => 'Thursday',
                    'friday'    => 'Friday',
                    'saturday'  => 'Saturday',
                    'sunday'    => 'Sunday',
                ),
            'timezone' => 'Europe/Amsterdam',
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
            'shipping_dpdbenelux_asynchronous'          => true,
            'shipping_dpdbenelux_asynchronous_from'     => 10,
            'shipping_dpdbenelux_consignor_eori_number' => 'abc10',
            'shipping_dpdbenelux_export_hsc_source'        => 1,
            'shipping_dpdbenelux_export_hsc_default'       => 'hsc default',
            'shipping_dpdbenelux_export_value_source'      => 2,
            'shipping_dpdbenelux_export_origin_country_source' => 3,
            'shipping_dpdbenelux_export_vat_number_source' => 1,
            'shipping_dpdbenelux_weight_default' => null,
            'shipping_dpdbenelux_sender_phone' => '123456',
            'shipping_dpdbenelux_export_origin_country_default' => null,

            'shipping_dpdbenelux'                       =>
                array(
                    'predict'          =>
                        array(
                            'title'        => 'Predict',
                            'description'  => '',
                            'cost'         => '4,44',
                            'tax_class_id' => '9',
                            'geo_zone_id'  => '0',
                            'status'       => '1',
                            'sort_order'   => '',
                        ),
                    'parcelshop'       =>
                        array(
                            'title'                  => 'parcelshop',
                            'description'            => '',
                            'cost'                   => '2,23',
                            'tax_class_id'           => '9',
                            'geo_zone_id'            => '0',
                            'status'                 => '1',
                            'sort_order'             => '',
                            'google_maps_api_key'    => '',
                            'google_maps_width'      => '',
                            'google_maps_width_type' => 'pixels',
                            'google_maps_height'     => '',
                            'number_of_shops'        => '10',
                        ),
                    'saturday'         =>
                        array(
                            'title'          => '',
                            'description'    => '',
                            'cost'           => '',
                            'tax_class_id'   => '0',
                            'geo_zone_id'    => '0',
                            'status'         => '0',
                            'sort_order'     => '',
                            'show_from_day'  => '',
                            'show_till_day'  => '',
                            'show_from_time' => '',
                            'show_till_time' => '',
                        ),
                    'classic_saturday' =>
                        array(
                            'title'          => '',
                            'description'    => '',
                            'cost'           => '',
                            'tax_class_id'   => '0',
                            'geo_zone_id'    => '0',
                            'status'         => '0',
                            'sort_order'     => '',
                            'show_from_day'  => '',
                            'show_till_day'  => '',
                            'show_from_time' => '',
                            'show_till_time' => '',
                        ),
                    'classic'          =>
                        array(
                            'title'        => 'classic',
                            'description'  => '',
                            'cost'         => '',
                            'tax_class_id' => '0',
                            'geo_zone_id'  => '0',
                            'status'       => '0',
                            'sort_order'   => '',
                        ),
                    'guarantee18'      =>
                        array(
                            'title'        => 'Guarantee 18:00',
                            'description'  => '',
                            'cost'         => '10,50',
                            'tax_class_id' => '9',
                            'geo_zone_id'  => '0',
                            'status'       => '1',
                            'sort_order'   => '',
                        ),
                    'express12'        =>
                        array(
                            'title'        => 'Express 12:00',
                            'description'  => '',
                            'cost'         => '32,50',
                            'tax_class_id' => '9',
                            'geo_zone_id'  => '0',
                            'status'       => '1',
                            'sort_order'   => '',
                        ),
                    'express10'        =>
                        array(
                            'title'        => '',
                            'description'  => '',
                            'cost'         => '',
                            'tax_class_id' => '0',
                            'geo_zone_id'  => '0',
                            'status'       => '0',
                            'sort_order'   => '',
                        ),
                ),
        );
    }

}
