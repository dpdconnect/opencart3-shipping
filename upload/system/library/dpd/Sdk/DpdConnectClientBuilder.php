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

namespace DpdConnect\Sdk;

use DPD\DpdAuthentication;
use DPD\DpdConfiguration;
use DpdConnect\Sdk\ClientBuilder as SuperClass;
use DpdConnect\Sdk\Common\Authentication;
use DpdConnect\Sdk\Objects\MetaData;
use DpdConnect\Sdk\Objects\ObjectFactory;
use DpdConnect\Sdk\Resources\Authentication as AuthenticationApi;
use DpdConnect\Sdk\Common\AuthenticatedHttpClient;
use DpdConnect\Sdk\Common\HttpClient;

/**
 * Class DpdConnectClientBuilder
 *
 * @package DpdConnect\Sdk
 */
class DpdConnectClientBuilder extends SuperClass
{
    /**
     * @var ClientInterface
     */
    private static $instance = null;

    /**
     * DpdConnectClientBuilder constructor.
     * @param null $endpoint
     * @param null $meta
     */
    public function __construct($endpoint = null, $meta = null)
    {
        if($meta === null) {
            $meta = ObjectFactory::create(MetaData::class, [
                'webshopType' => 'Opencart',
                'webshopVersion' => VERSION,
                'pluginVersion' => '1.0.1',
            ]);
        }

        parent::__construct($endpoint, $meta);
    }

    /**
     * Workaround for ShipmentApi::create only returning http statusCode
     *
     * @deprecated
     * @param  Authentication                   $authentication from Authentication::fromJwtToken or ::fromPassword
     * @param  string baseurl of dpdconnect api
     * @return AuthenticatedHttpClient
     */
    public function buildAuthenticatedHttpClient(Authentication $authentication, $endpoint)
    {
        $meta = [
            'webshopType' => 'Opencart',
            'webshopVersion' => VERSION,
            'pluginVersion' => '1.0.1',
        ];

        $httpClient = new HttpClient($endpoint);
        $httpClient->setMeta($meta);

        $authenticationApi = new AuthenticationApi($httpClient);

        return new AuthenticatedHttpClient($httpClient, $authenticationApi, $authentication);
    }

    public static function buildAuthenticatedClientUsingDpdAuthentication(DpdAuthentication $dpdAuthentication)
    {
        if (self::$instance === null) {
            $clientBuilder = new self(DpdConfiguration::getDpdConnectUrl());

            self::$instance = $dpdAuthentication->getAccessToken()
                ? $clientBuilder->buildAuthenticatedByJwtToken($dpdAuthentication->getAccessToken())
                : $clientBuilder->buildAuthenticatedByPassword($dpdAuthentication->getUsername(), $dpdAuthentication->getPassword());
        }

        return self::$instance;
    }
}
