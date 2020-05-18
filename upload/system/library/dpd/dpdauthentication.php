<?php
/**
 * This file is part of the Prestashop Shipping module of DPD Nederland B.V.
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
namespace DPD;

use Squareup\Exception;
use Symfony\Component\Validator\ExecutionContext;

class DpdAuthentication
{
    public $controller;

    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $loader = new \Loader($registry);
        $loader->library('dpd/dpdconfiguration');
        $this->config = $registry->get('config');
    }


    public function getUsername()
    {
        return DpdConfiguration::getValue('username');
    }

    public function getPassword()
    {
        $encryptedPassword = DpdConfiguration::getValue('password');
        $key = DpdConfiguration::getValue('config_encryption', true);
        return $this->registry->get('encryption')->decrypt($key, $encryptedPassword);
    }

    public function encryptPassword($password)
    {
        $key = $this->config->get('config_encryption');
        // may throw Warning: openssl_encrypt(): Using an empty Initialization Vector (iv) is potentially insecure and not recommended
        return @$this->registry->get('encryption')->encrypt($key, $password);
    }

    public function getDepot()
    {
        return DpdConfiguration::getValue('sending_depot');
    }

    public function getAccessToken($postEnvironmentId = null)
    {
        $accesTokenCreatedTimestamp =  DpdConfiguration::getValue('access_token_created');
        // need to check if the token is older then 12 hours or is not set if so generate a new one.
        if($postEnvironmentId != null) {
            DpdConfiguration::updateValue('environment', $postEnvironmentId);
        }

        if(((int)$accesTokenCreatedTimestamp > time() - 12 * 60 * 60) 
            && DpdConfiguration::getValue('access_token') != (null || false) 
            && DpdConfiguration::getValue('access_token_environment') == DpdConfiguration::getValue('environment')
        ) {
            // get the cached token
            return DpdConfiguration::getValue('access_token');
        }

        return null;
    }

    public function setAccessToken($token)
    {
        $this->config->set('shipping_dpdbenelux_access_token', $token);
        $this->config->set('shipping_dpdbenelux_access_token_created', time());
        $this->config->set('shipping_dpdbenelux_access_token_environment', DpdConfiguration::getValue('environment'));
    }

    public function isConfigured()
    {
        $configurations['username'] = DpdConfiguration::getValue('username');
        $configurations['password'] = DpdConfiguration::getValue('password');
        $configurations['company'] = DpdConfiguration::getValue('company');
        $configurations['street'] = DpdConfiguration::getValue('street');
        $configurations['postalcode'] = DpdConfiguration::getValue('postalcode');
        $configurations['place'] = DpdConfiguration::getValue('place');
        $configurations['country'] = DpdConfiguration::getValue('country');
        $output = true;
        foreach ($configurations as $configuration){
            if(empty($configuration)) {
                $output =  false;
            }
        }
        return $output;

    }
}