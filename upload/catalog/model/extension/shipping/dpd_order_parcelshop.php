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

class ModelExtensionShippingDpdOrderParcelshop extends Model
{

    public function addOrEditParcelshop($orderId, $parcelshopId, $parcelshopCompany, $parcelshopStreet, $parcelshopZipcode, $parcelshopCity, $parcelshopCountry)
    {

        $parcelShop = $this->getParcelshop($orderId);
        if(count($parcelShop) !== 0) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "dpd_order_parcelshop` 
                SET `parcelshop_id` = '" . $this->db->escape($parcelshopId) . "',  
                `parcelshop_company` = '" . $this->db->escape($parcelshopCompany) . "',  
                `parcelshop_street` = '" . $this->db->escape($parcelshopStreet) . "',  
                `parcelshop_zipcode` = '" . $this->db->escape($parcelshopZipcode) . "',  
                `parcelshop_city` = '" . $this->db->escape($parcelshopCity) . "',  
                `parcelshop_country` = '" . $this->db->escape($parcelshopCountry) . "',   
                date_modified = NOW()
                WHERE `order_id` = '" . $this->db->escape($orderId) . "'
            "
            );
            return $parcelShop['dpd_order_parcelshop_id'];
        }

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "dpd_order_parcelshop` 
            SET `order_id` = '" . $this->db->escape($orderId) . "',  
            `parcelshop_id` = '" . $this->db->escape($parcelshopId) . "',  
            `parcelshop_company` = '" . $this->db->escape($parcelshopCompany) . "',  
            `parcelshop_street` = '" . $this->db->escape($parcelshopStreet) . "',  
            `parcelshop_zipcode` = '" . $this->db->escape($parcelshopZipcode) . "',  
            `parcelshop_city` = '" . $this->db->escape($parcelshopCity) . "',  
            `parcelshop_country` = '" . $this->db->escape($parcelshopCountry) . "',   
            date_added = NOW(),
            date_modified = NOW()
		"
        );

        $dpd_shipment_id = $this->db->getLastId();
        return $dpd_shipment_id;
    }


    public function getParcelshop($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dpd_order_parcelshop` WHERE `order_id` = '" . $this->db->escape($order_id) . "'");

        return $query->row;
    }

}