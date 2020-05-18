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

namespace DPD;

class DpdConfiguration
{
    const connectUrls = [
        1 => 'https://api.dpdconnect.nl', // live
        2 => 'http://app:5000', // demo
    ];
    const carrierNames = [
    'predict' => [
    'name'        => 'DPD Predict',
    'description' => 'DPD Predict delivery',
    'type'        => 'B2C'
    ],

    'parcelshop' => [
    'name'        => 'DPD Parcelshop',
    'description' => 'DPD Parcelshop delivery',
    'type'        => 'B2C'
    ],

    'saturday' => [
    'name'        => 'DPD Saturday',
    'description' => 'DPD Saturday delivery',
    'type'        => 'B2C',
    ],

    'classic_saturday' => [
    'name'        => 'DPD Classic Saturday',
    'description' => 'DPD Saturday delivery',
    'type'        => 'B2B'
    ],

    'classic' => [
    'name'        => 'DPD Classic',
    'description' => 'DPD Classic delivery',
    'type'        => 'B2B'
    ],

    'guarantee18' => [
    'name'        => 'Guarantee 18:00',
    'description' => 'DPD Guarantee 18:00 delivery',
    'type'        => 'B2B'
    ],

    'express12' => [
    'name'        => 'Express 12:00',
    'description' => 'DPD Express 12:00 delivery',
    'type'        => 'B2B'
    ],

    'express10' => [
    'name'        => 'Express 10:00',
    'description' => 'DPD Express 10:00 delivery',
    'type'        => 'B2B'
    ],
    ];

    public static $code;
    private static $registry;
    private static $loader;
    private static $settingModel;
    private static $config;

    public function __construct($registry)
    {
        self::$code = 'shipping_dpdbenelux';
        self::$registry = $registry;
        self::$loader = new \Loader(self::$registry);
        self::$loader->model('setting/setting');
        self::$settingModel = self::$registry->get('model_setting_setting');
        self::$config = $registry->get('config');
    }

    public static function updateValue($key, $value, $code_included = false)
    {
        if($code_included == false) {
            $key = self::$code . '_' . strtolower($key);
        }
        $settingData = self::$settingModel->getSetting(self::$code);
        $settingData[$key] = $value;
        self::$settingModel->editSetting(self::$code, $settingData);
    }

    public static function updateMultipleValues($data, $code_included = false)
    {
        $settingData = self::$settingModel->getSetting(self::$code);
        if ($code_included == false) {
            foreach ($data as $key => $value) {
                $newKey = self::$code . '_' . $key;
                $data[$newKey] = $value;
                unset($data[$key]);
            }
        }

        self::$settingModel->editSetting(self::$code, array_merge($settingData, $data));
    }

    public static function getValue($key, $code_included = false)
    {
        if($code_included == false) {
            $key = self::$code . '_' . $key;
        }

        return self::$settingModel->getSettingValue($key);
    }

    public static function getDpdConnectUrl()
    {
        $url = self::$settingModel->getSettingValue('shipping_dpdbenelux_url');

        if ($url !== '') {
            return $url;
        }

        return self::connectUrls[self::getValue('environment')];
    }

    public static function isSaturdayAllowed($carrierKey)
    {
        // Carrier key so it supports both classic(b2b) and b2c saturday delivery

        $showfromday = self::$config->get('shipping_dpdbenelux_'.$carrierKey.'_show_from_day');
        $showfromtime = self::$config->get('shipping_dpdbenelux_'.$carrierKey.'_show_from_time');
        $showtilltime = self::$config->get('shipping_dpdbenelux_'.$carrierKey.'_show_till_time');
        $showtillday = self::$config->get('shipping_dpdbenelux_'.$carrierKey.'_show_till_day');
        if(empty($showfromday) || empty($showfromtime) || empty($showtillday) || empty($showtilltime)) {
            return false;
        }

        $showfromtime = explode(':', $showfromtime);
        $firstDate = new \DateTime($showfromday . ' this week ' . $showfromtime[0] . ' hours ' . $showfromtime[1] . ' minutes 00 seconds');
        $showtilltime = explode(':', $showtilltime);
        $lastDate = new \DateTime($showtillday . ' this week ' . $showtilltime[0] . ' hours ' . $showtilltime[1] . ' minutes 59 seconds');

        $today = new \DateTime();
        
        return $today >= $firstDate && $today <= $lastDate;
    }
}




