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

namespace DPDBenelux\Connect\Plugin\Tests\Label;

use DpdConnect\Label\Entity\Batch;
use DpdConnect\Label\LabelRequestFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LabelRequestFactoryTest extends TestCase
{
    /** @var LabelRequestFactory */
    private $subject;
    /** @var MockObject|\DpdConnect\Sdk\Resources\Country */
    private $countryResource;

    public static function setUpBeforeClass()
    {
        require_once 'tests/Mock/Config.php';
        require_once 'tests/Mock/DpdAuthentication.php';
    }

    protected function setUp()
    {
        $this->config = new \Config();
        $this->client = $this->createMock('\DpdConnect\Sdk\Client');

        $this->subject = new LabelRequestFactory($this->config, $this->client);

        $this->countryResource = $this->createMock('\DpdConnect\Sdk\Resources\Country');
        $this->client->expects($this->any())
            ->method('getCountries')
            ->willReturn($this->countryResource);
        $refProp = new \ReflectionProperty(LabelRequestFactory::class, 'client');
        $refProp->setAccessible(true);
        $refProp->setValue($this->subject, $this->client);
    }

    public function testRequestParcelshop()
    {
        $parcelCount = '1';
        $batch = new Batch();
        $batch->setShipments([[ 'order' => $this->getOrderParcelshop(), 'is_return' => false]]);
        $this->countryResource->expects($this->once())
            ->method('getList')
            ->willReturn($this->getCountriesList());

        $labelRequest = $this->subject->getLabelRequest($batch, $parcelCount);
        self::assertEquals($this->getRequestParcelshop(), $labelRequest);
    }

    public function testRequestSaturday()
    {
        $parcelCount = '1';
        $batch = new Batch();
        $batch->setShipments([[ 'order' => $this->getOrderSaturday(), 'is_return' => false]]);
        $this->countryResource->expects($this->once())
            ->method('getList')
            ->willReturn($this->getCountriesList());

        $labelRequest = $this->subject->getLabelRequest($batch, $parcelCount);
        self::assertEquals($this->getRequestSaturday(), $labelRequest);
    }

    public function testRequestGuarantee18()
    {
        $parcelCount = '1';
        $orderData = $this->getOrderSaturday();
        $orderData['shipping_code'] = 'dpdbenelux.guarantee18';
        $batch = new Batch();
        $batch->setShipments([[ 'order' => $orderData, 'is_return' => false]]);
        $this->countryResource->expects($this->once())
            ->method('getList')
            ->willReturn($this->getCountriesList());

        $labelRequest = $this->subject->getLabelRequest($batch, $parcelCount);
        self::assertEquals('shop user', $labelRequest['shipments'][0]['receiver']['contact']);
    }

    private function getOrderSaturday()
    {
        return array (
            'order_id' => '6',
            'invoice_no' => '0',
            'invoice_prefix' => 'INV-2019-00',
            'store_id' => '0',
            'store_name' => 'Your Store',
            'store_url' => 'http://opencart.test:8888/',
            'customer_id' => '1',
            'customer' => 'shop user',
            'customer_group_id' => '1',
            'firstname' => 'shop',
            'lastname' => 'user',
            'email' => 'info@example.nl',
            'telephone' => '0543122874',
            'custom_field' => [
                2 => 'Customer VAT',
            ],
            'payment_firstname' => 'shop',
            'payment_lastname' => 'user',
            'payment_company' => '',
            'payment_address_1' => 'userstraat',
            'payment_address_2' => '',
            'payment_postcode' => '',
            'payment_city' => 'userstad',
            'payment_zone_id' => '2329',
            'payment_zone' => 'Drenthe',
            'payment_zone_code' => 'DR',
            'payment_country_id' => '150',
            'payment_country' => 'Netherlands',
            'payment_iso_code_2' => 'NL',
            'payment_iso_code_3' => 'NLD',
            'payment_address_format' => '',
            'payment_custom_field' =>
                array (
                    1 => 'Payment VAT',
                    4 => 'Other custom field value'
                ),
            'payment_method' => 'Cash On Delivery',
            'payment_code' => 'cod',
            'shipping_firstname' => 'shop',
            'shipping_lastname' => 'user',
            'shipping_company' => '',
            'shipping_address_1' => 'userstraat',
            'shipping_address_2' => '',
            'shipping_postcode' => '',
            'shipping_city' => 'userstad',
            'shipping_zone_id' => '2329',
            'shipping_zone' => 'Drenthe',
            'shipping_zone_code' => 'DR',
            'shipping_country_id' => '150',
            'shipping_country' => 'Netherlands',
            'shipping_iso_code_2' => 'NL',
            'shipping_iso_code_3' => 'NLD',
            'shipping_address_format' => '',
            'shipping_custom_field' =>
                array (
                    3 => 'Something else',
                ),
            'shipping_method' => '<strong>Saturday</strong> - ',
            'shipping_code' => 'dpdbenelux.saturday',
            'comment' => '',
            'total' => '131.0000',
            'reward' => 400,
            'order_status_id' => '1',
            'order_status' => 'Pending',
            'affiliate_id' => '0',
            'affiliate_firstname' => '',
            'affiliate_lastname' => '',
            'commission' => '0.0000',
            'language_id' => '1',
            'language_code' => 'en-gb',
            'currency_id' => '2',
            'currency_code' => 'USD',
            'currency_value' => '1.00000000',
            'ip' => '172.19.0.1',
            'forwarded_ip' => '',
            'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0',
            'accept_language' => 'en-US,en;q=0.5',
            'date_added' => '2019-04-01 13:05:44',
            'date_modified' => '2019-04-01 13:05:52',
            'dpdParcelShop' =>
                array (
                ),
            'dpdWeightInDecagrams' => 14640000,
        );
    }

    private function getOrderParcelshop()
    {
        return array (
            'order_id' => '7',
            'invoice_no' => '0',
            'invoice_prefix' => 'INV-2019-00',
            'store_id' => '0',
            'store_name' => 'Your Store',
            'store_url' => 'http://opencart.test:8888/',
            'customer_id' => '1',
            'customer' => 'shop user',
            'customer_group_id' => '1',
            'firstname' => 'shop',
            'lastname' => 'user',
            'email' => 'info@example.nl',
            'telephone' => '0543122874',
            'custom_field' => NULL,
            'payment_firstname' => 'shop',
            'payment_lastname' => 'user',
            'payment_company' => '',
            'payment_address_1' => 'Brugstraat 1',
            'payment_address_2' => '',
            'payment_postcode' => '7891 AP ',
            'payment_city' => 'Klazienaveen',
            'payment_zone_id' => '2329',
            'payment_zone' => 'Drenthe',
            'payment_zone_code' => 'DR',
            'payment_country_id' => '150',
            'payment_country' => 'Netherlands',
            'payment_iso_code_2' => 'NL',
            'payment_iso_code_3' => 'NLD',
            'payment_address_format' => '',
            'payment_custom_field' =>
                array (
                ),
            'payment_method' => 'Cash On Delivery',
            'payment_code' => 'cod',
            'shipping_firstname' => 'DPD Parcelshop:',
            'shipping_lastname' => 'OFFICE &amp; CARTRIDGE',
            'shipping_company' => '',
            'shipping_address_1' => 'WILHELMINASTRAAT 89',
            'shipping_address_2' => '',
            'shipping_postcode' => '7811 JK',
            'shipping_city' => 'Emmen',
            'shipping_zone_id' => '2329',
            'shipping_zone' => 'Drenthe',
            'shipping_zone_code' => 'DR',
            'shipping_country_id' => '150',
            'shipping_country' => 'NL',
            'shipping_iso_code_2' => 'NL',
            'shipping_iso_code_3' => 'NLD',
            'shipping_address_format' => '',
            'shipping_custom_field' =>
                array (
                    1 => 'Shipping VAT',
                ),
            'shipping_method' => '<strong>parcelshop</strong> - ',
            'shipping_code' => 'dpdbenelux.parcelshop',
            'comment' => '',
            'total' => '123.0000',
            'reward' => 400,
            'order_status_id' => '1',
            'order_status' => 'Pending',
            'affiliate_id' => '0',
            'affiliate_firstname' => '',
            'affiliate_lastname' => '',
            'commission' => '0.0000',
            'language_id' => '1',
            'language_code' => 'en-gb',
            'currency_id' => '3',
            'currency_code' => 'EUR',
            'currency_value' => '1.00000000',
            'ip' => '172.19.0.1',
            'forwarded_ip' => '',
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/73.0.3683.75 Chrome/73.0.3683.75 Safari/537.36',
            'accept_language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
            'date_added' => '2019-04-05 08:45:09',
            'date_modified' => '2019-04-05 08:45:14',
            'dpdParcelShop' =>
                array (
                    'dpd_order_parcelshop_id' => '1',
                    'order_id' => '7',
                    'date_added' => '2019-04-05 08:45:09',
                    'date_modified' => '2019-04-05 08:45:09',
                    'parcelshop_id' => 'NL11631',
                    'parcelshop_company' => 'OFFICE &amp; CARTRIDGE',
                    'parcelshop_street' => 'WILHELMINASTRAAT 89',
                    'parcelshop_zipcode' => '7811 JK',
                    'parcelshop_city' => 'Emmen',
                    'parcelshop_country' => 'NL',
                ),
            'dpdWeightInDecagrams' => 14640000,
        );
    }

    private function getRequestParcelshop()
    {
        return array (
            'printOptions' =>
                array (
                    'printerLanguage' => 'PDF',
                    'paperFormat' => 'A4',
                    "verticalOffset"  => 0,
                    "horizontalOffset"  => 0,
                ),
            'createLabel' => true,
            'shipments' =>
                array (
                    0 =>
                        array (
                            'sendingDepot' => '0522',
                            'sender' =>
                                array (
                                    'name1' => 'testcompany',
                                    'street' => 'teststreet 1',
                                    'postalcode' => '1111AB',
                                    'city' => 'teststad',
                                    'country' => 'NL',
                                    'phoneNumber' => '123456',
                                    'commercialAddress' => true,
                                ),
                            'receiver' =>
                                array (
                                    'name1' => 'shop user',
                                    'name2' => '',
                                    'street' => 'Brugstraat 1 ',
                                    'housenumber' => '',
                                    'postalcode' => '7891 AP ',
                                    'city' => 'Klazienaveen',
                                    'country' => 'NL',
                                    'phoneNumber' => '0543122874',
                                    'commercialAddress' => true,
                                ),
                            'parcels' =>
                                array (
                                    0 =>
                                        array (
                                            'customerReferences' => ['7'],
                                            'weight' => 14640000,
                                        ),
                                ),
                            'product' =>
                                array (
                                    'productCode' => 'CL',
                                    'parcelshopId' => 'NL11631',
                                ),
                            'notifications' => [
                                0 => [
                                    "subject" => "parcelshop",
                                    'channel' => 'EMAIL',
                                    'value' => 'info@example.nl',
                                ],
                            ],
                            'orderId' => '7',
                            'weight' => 14640000,
                        ),
                ),
        );
    }

    private function getRequestSaturday()
    {
        return array (
            'printOptions' =>
                array (
                    'printerLanguage' => 'PDF',
                    'paperFormat' => 'A4',
                    "verticalOffset"  => 0,
                    "horizontalOffset"  => 0,
                ),
            'createLabel' => true,
            'shipments' =>
                array (
                    0 =>
                        array (
                            'sendingDepot' => '0522',
                            'sender' =>
                                array (
                                    'name1' => 'testcompany',
                                    'street' => 'teststreet 1',
                                    'postalcode' => '1111AB',
                                    'city' => 'teststad',
                                    'country' => 'NL',
                                    'phoneNumber' => '123456',
                                    'commercialAddress' => true,
                                ),
                            'receiver' =>
                                array (
                                    'name1' => 'shop user',
                                    'name2' => '',
                                    'street' => 'userstraat ',
                                    'housenumber' => '',
                                    'postalcode' => '',
                                    'city' => 'userstad',
                                    'country' => 'NL',
                                    'phoneNumber' => '0543122874',
                                    'commercialAddress' => true,
                                ),
                            'parcels' =>
                                array (
                                    0 =>
                                        array (
                                            'customerReferences' => ['6'],
                                            'weight' => 14640000,
                                        ),
                                ),
                            'product' =>
                                array (
                                    'productCode' => 'CL',
                                    'saturdayDelivery' => true,
                                ),
                            'notifications' => [
                                0 => [
                                    "subject" => "predict",
                                    'channel' => 'EMAIL',
                                    'value' => 'info@example.nl',
                                ],
                            ],
                            'orderId' => '6',
                            'weight' => 14640000,
                        ),
                ),
        );
    }

    private function getCountriesList()
    {
        return [
            ['country' => 'NL', 'singleMarket' => true],
        ];
    }
}
