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

namespace DPDBenelux\Connect\Plugin\Tests\Functional;

use DpdConnect\Sdk\Common\Authentication;
use DpdConnect\Sdk\Resources\Authentication as AuthenticationApi;
use DpdConnect\Sdk\Common\AuthenticatedHttpClient;
use DpdConnect\Sdk\Common\HttpClient;
use PHPUnit\Framework\TestCase;
use DpdConnect\Sdk\Exceptions\HttpException;
use DpdConnect\Sdk\DpdConnectClientBuilder;


class SdkTest extends TestCase
{

    public function testParcelShops()
    {
        $clientBuilder = new DpdConnectClientBuilder();
        $authentication = Authentication::fromPassword('admin', 'admin');
        $client = $clientBuilder->buildAuthenticatedHttpClient($authentication, 'https://dpd-connect-api.test.xdemo.nl');
        $query = ['longitude'  => 52.992,
                  'latitude'   => 6.565,
                  'countryIso' => 'nl',
                  'limit' => 10,
            ];
        try {
            $response = $client->sendRequest('GET', 'api/connect/v1/parcelshop', $query);
            $result = json_decode($response[1], true);
            var_dump($result);
            if (isset($result['_embedded'])) {
                var_dump($result['_embedded']);
            }

        } catch (HttpException $e) {
            var_dump($e->getResponseErrors());
            die (get_class($e). ' '. $e->getMessage());
        }
    }


    public function testShipment()
    {
        $baseUrl = 'https://dpd-connect-api.test.xdemo.nl';
        $clientBuilder = new DpdConnectClientBuilder();
        $authentication = Authentication::fromPassword('admin', 'admin');
        $authenticatedHttpClient = $clientBuilder->buildAuthenticatedHttpClient($authentication, $baseUrl);

        try {
            $response = $authenticatedHttpClient->sendRequest('POST',  'api/connect/v1/shipment', [], [], $this->getLabelJson());
        } catch (\Exception $e) {
            #TODO:  Log the exception
            die (get_class($e). ' '. $e->getMessage());
        }
       // var_dump($response); die();
        $statusCode = $response[0];
        $body = $response[1];
        $batchResponse = json_decode($body, true);
        var_dump($batchResponse);
    }

    private function getLabelJson()
    {
        return '{
    "printOptions": {
        "printerLanguage": "PDF",
        "paperFormat": "A4",
        "verticalOffset": 0,
        "horizontalOffset": 0
    },
    "createLabel": true,
    "shipments": [
        {
            "orderId": "SdkTestOrder",
            "sendingDepot": "0522",
            "volume": "100060040",
            "weight": 2000,
            "expectedSendingDateTime": "2019-12-12T00:00:00Z",
            "sender": {
                "name1": "Sender Name",
                "name2": "Sender Name2",
                "street": "Senderstreet",
                "housenumber": "12",
                "state": "DR",
                "country": "NL",
                "postalcode": "1234AA",
                "city": "Eindhoven",
                "globalLocationNumber": 9501101530003,
                "contactPerson": "Contact name",
                "phoneNumber": "0612345678",
                "faxNumber": "0501234567",
                "comment": "Lorem ipsum",
                "companyName": "Y-Interactief",
                "commercialAddress": true,
                "floor": "Fifth",
                "building": "AB",
                "department": "ICT",
                "website": "www.example.com",
                "vatNumber": "NL02123",
                "eoriNumber": "123",
                "email": "sender@example.com"
            },
            "receiver": {
                "name1": "Receiver Name",
                "street": "Receiverstreet",
                "housenumber": "11",
                "state": "DR",
                "country": "NL",
                "postalcode": "1234AA",
                "city": "London",
                "contactPerson": "Contact name",
                "phoneNumber": "0612345678",
                "faxNumber": "0501234567",
                "companyName": "Y-Interactief",
                "commercialAddress": true,
                "floor": "Fifth",
                "building": "AB",
                "department": "ICT",
                "doorCode": "AB12",
                "vatNumber": "NL02123",
                "eoriNumber": "123",
                "email": "sender@example.com"
            },
            "product": {
                "productCode": "CL",
                "homeDelivery": true,
                "saturdayDelivery": true,
                "tyres": false
            },
            "parcels": [
                {
                   "customerReferences": [
                        "123",
                        "456",
                        "546",
                        "123"
                    ],
                    "volume": "100060040",
                    "weight": 3000,
                    "cod": {
                        "amount": 400,
                        "currency": "EUR",
                        "paymentMethod": "CASH"
                    }
                },
                {
                   "customerReferences": [
                        "123",
                        "456",
                        "546",
                        "123"
                    ],
                    "volume": "100060040",
                    "weight": 3000
                }
            ],
            "notifications": [
                {
                    "subject": "parcelshop",
                    "channel": "SMS",
                    "value": "Lorem ipsum",
                    "language": "NL"
                },
                {
                    "subject": "predict",
                    "channel": "EMAIL",
                    "value": "Lorem ipsum",
                    "language": "NL"
                }
            ]
        }
    ]
}';
    }
}
