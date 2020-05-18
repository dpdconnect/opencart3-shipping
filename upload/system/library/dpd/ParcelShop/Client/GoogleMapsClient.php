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

namespace DpdConnect\ParcelShop\Client;

class GoogleMapsClient
{
    /**
     *
     * @var \Log 
     */
    private $log;
    /**
     *
     * @var string  
     */
    private $apiKey;

    /**
     * GoogleMapsClient constructor.
     *
     * @param string $apiKey
     * @param \Log   $log
     */
    public function __construct($apiKey, $log)
    {
        $this->apiKey = $apiKey;
        $this->log = $log;
    }

    /**
     *
     * @param  string $postcode    Zipcode
     * @param  string $countryCode 2 letter country code
     * @return array|null The Geo Coordinates of the center of the map
     */
    public function getGoogleMapsCenter($postcode, $countryCode)
    {
        try {
            $data = urlencode('country:' . $countryCode . '|postal_code:' . $postcode);

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?key=' . $this->apiKey . '&components=' . $data . '&sensor=false';
            $source = file_get_contents($url);
            $obj = json_decode($source);
        } catch (\Exception $ex) {
            $this->log->write('GoogleMapsClient::getGoogleMapsCenter '. get_class($ex). ': '. $ex->getMessage(). ' address: '. $address);
            return null;
        }

        if (isset($obj->error_message)) {
            $this->log->write('GoogleMapsClient::getGoogleMapsCenter error: '. $obj->error_message. ' address: '. $address);
            return null;
        }

        if (empty($obj->results)) {
            $this->log->write('GoogleMapsClient::getGoogleMapsCenter no results for address: '. $address);
            return null;
        }

        return [$obj->results[0]->geometry->location->lat, $obj->results[0]->geometry->location->lng];
    }
}
