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


use DPD\DpdAuthentication;
use Cart\User;

class IndexSetAction
{
    /**
     *
     * @var \Language  
     */
    private $language;
    /**
     *
     * @var User  
     */
    private $user;
    /**
     *
     * @var \Config  
     */
    private $config;
    /**
     *
     * @var \ModelSettingSetting  
     */
    private $model_setting_setting;
    /**
     *
     * @var DpdAuthentication  
     */
    private $dpdauthentication;
    /**
     *
     * @var \Encryption  
     */
    private $encryption;
    /**
     *
     * @var array  
     */
    private $error = [];

    /**
     * @var array
     */
    private $error_fields = [];


    /**
     * IndexSetAction constructor.
     *
     * @param \Language            $language
     * @param User                 $user
     * @param \Config              $config
     * @param \ModelSettingSetting $model_setting_setting
     * @param DpdAuthentication    $dpdauthentication
     * @param \Encryption          $encryption
     */
    public function __construct(
        \Language $language,
        User $user,
        \Config $config,
        $model_setting_setting, // Proxy..
        DpdAuthentication $dpdauthentication,
        \Encryption $encryption
    ) {
        $this->language = $language;
        $this->user = $user;
        $this->config = $config;
        $this->model_setting_setting = $model_setting_setting;
        $this->dpdauthentication = $dpdauthentication;
        $this->encryption = $encryption;
    }

    /**
     *
     * @param  array $postData
     * @return array errors
     */
    public function perform(array $postData)
    {
        // We need to the password encrypted
        if (empty($postData['shipping_dpdbenelux_password'])) {
            // if they dont change the password get the encrypted password from the config
            $postData['shipping_dpdbenelux_password'] = $this->config->get('shipping_dpdbenelux_password');
        } else {
            // encrypt the password
            $postData['shipping_dpdbenelux_password'] = $this->dpdauthentication->encryptPassword($postData['shipping_dpdbenelux_password']);
        }
        
        if ($this->validate($postData)) {
            $this->model_setting_setting->editSetting('shipping_dpdbenelux', $postData);
        }
        return $this->error;
    }

    /**
     *
     * @param  array $postData
     * @return bool valid
     */
    private function validate(array $postData)
    {
        if (!$this->user->hasPermission('modify', 'extension/shipping/dpdbenelux')) {
            $this->error['warning'] = $this->language->get('error_permission');

            return false;
        }

        $required_fields = [
            'shipping_dpdbenelux_username',
            'shipping_dpdbenelux_sending_depot',
            'shipping_dpdbenelux_account_type',
            'shipping_dpdbenelux_environment',
            'shipping_dpdbenelux_paper_format',
            'shipping_dpdbenelux_sender_company_name',
            'shipping_dpdbenelux_sender_street',
            'shipping_dpdbenelux_sender_postal_code',
            'shipping_dpdbenelux_sender_place',
            'shipping_dpdbenelux_sender_country_code',
            'shipping_dpdbenelux_sender_phone'
        ];

        $required_field_missing = false;
        $missing_fields = [];
        foreach($required_fields as $required_field) {
            if($postData[$required_field] == null || $postData[$required_field] == '0') {
                $required_field_missing = true;
                $missing_fields[] = $required_field;
            }
        }

        if(true === $required_field_missing) {
            $this->error[] = $this->language->get('error_not_filled_in');
            $this->error_fields = $missing_fields;
            array_merge($this->error, $missing_fields);

            return false;
        }

        if ($postData['shipping_dpdbenelux_asynchronous'] && !is_numeric($postData['shipping_dpdbenelux_asynchronous_from'])) {
            $this->error[] = $this->language->get('error_asynchronous_from_not_numeric');
        }

        if (!empty($token['error'])) {
            $this->error[] = $this->language->get('error_login');

            return false;
        }

        return !$this->error;
    }
}
