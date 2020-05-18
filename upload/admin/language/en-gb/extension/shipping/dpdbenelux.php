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

// Custom
$_['locale'] = 'en_GB';
$_['dpdbenelux_disabled_message'] = 'DPD Shipping methods are disabled. The current configuration will be ignored. Enable DPD Shipping by changing the status to Enabled';
$_['title'] = 'Title';
$_['entry_title'] = 'DPD Parcelservice';

$_['description'] = 'Description';
$_['entry_description'] = 'Shipment to your home';

// Heading
$_['heading_title']    = 'DPD Parcelservice';
$_['batches_heading_title'] = 'DPD Labels';
$_['shipments_heading_title'] = 'DPD Shipments';
$_['product_data_heading_title'] = 'DPD Export Data';
$_['title_shipment_history'] = 'DPD Shipment History';

// Text
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: You have modified DPD Parcelservice!';
$_['text_edit']        = 'Edit DPD Parcelservice';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_all_zones'] = 'All zones';
$_['text_none'] = '--- None ---';
$_['text_pixels'] = 'Pixels';
$_['text_percentage'] = 'Percent';
$_['text_generate'] = 'Generate';
$_['text_number_of_parcels']   = 'Number of Parcels';
$_['text_labels_queued'] = 'Queued label generation for';
$_['text_labels_generated'] = 'Generated labels for';
$_['text_shipments'] = 'shipments';
$_['text_parcels'] = 'Parcels';
$_['text_status'] = 'Status';
$_['text_labels_not_yet_generated'] = 'Labels have not yet been generated';
$_['text_status_request'] = 'Pending';
$_['text_status_queued'] = 'Queued';
$_['text_status_processing'] = 'Processing';
$_['text_status_failed'] = 'Failed';
$_['text_status_partially_failed'] = 'Partially Failed';
$_['text_status_success'] = 'Success';
$_['text_batch_list'] = 'Batches';
$_['text_shipment_list'] = 'Shipments';
$_['text_both'] = 'Both';
$_['text_shipment_history'] = 'History';
$_['text_data_only_required_for_export'] = 'These date are only required for export outside the European Single Market';
$_['text_return'] = 'return';
$_['text_using_timezone'] = 'Using timezone';

// Entry
$_['entry_cost']       = 'Cost';
$_['entry_tax_class']  = 'Tax Class';
$_['entry_geo_zone']   = 'Geo Zone';
$_['entry_status']     = 'Status';
$_['entry_sort_order'] = 'Sort Order';
$_['entry_google_maps_api_client_key'] = 'Google Maps API client key';
$_['entry_google_maps_api_server_key'] = 'Google Maps API server key';
$_['entry_google_maps_width'] = 'Map width';
$_['entry_google_maps_height'] = 'Map height in pixels';
$_['entry_number_of_shops'] = 'Max number of shops shown';
$_['entry_show_from_day'] = 'Show from day';
$_['entry_show_from_time'] = 'Show from time';
$_['entry_show_till_day'] = 'Show till day';
$_['entry_show_till_time'] = 'Show till time';
$_['entry_select_day'] = 'Select a day';
$_['entry_days_monday'] = 'Monday';
$_['entry_days_tuesday'] = 'Tuesday';
$_['entry_days_wednesday'] = 'Wednesday';
$_['entry_days_thursday'] = 'Thursday';
$_['entry_days_friday'] = 'Friday';
$_['entry_days_saturday'] = 'Saturday';
$_['entry_days_sunday'] = 'Sunday';
$_['entry_batch_id'] = 'Batch nr';
$_['entry_started'] = 'Started';
$_['entry_order_id'] = 'Order ID';
$_['entry_is_return'] = 'Return';
$_['entry_hsc'] =  'Harmonized System Code';
$_['entry_export_value'] = 'Customs Value €';
$_['entry_origin_country'] = 'Origin Country Code';
$_['entry_vat_number'] = 'Consignee VAT Number';

$_['enable_scp_ncp_choice'] = 'SCP/NCP choice';
$_['general_status']   = 'DPD Parcelservice status';

// Error
$_['errors'] = 'Errors';
$_['error_permission'] = 'Warning: You do not have permission to modify DPD Parcelservice!';
$_['error_not_filled_in'] = 'Please fill in all required fields';
$_['error_login'] = 'The delisId and Password combination is incorrect';
$_['error_no_orders_selected'] = 'Please select an order to print';
$_['error_no_soap_installed'] = 'You do not have the SOAP extension installed/activated which this extension requires. Contact your hosting provider enable this.';
$_['error_asynchronous_from_not_numeric'] = 'Please fill in a number for Asynchronous From';
$_['error_no_labels'] = 'There are no labels';
$_['error_generating_labels'] = 'Errors while generating labels:';
$_['error_payment_zipcode_required'] = 'Payment zipcode required for parcelshop delivery';
$_['error_shipping_zipcode_required'] = 'Shipping zipcode required';
$_['error_customs_only_once'] = 'For Shipments with Customs, Labels can be generated only once';
$_['error_customs_multiple_parcels'] = 'For Shipments with Customs, only one Parcel is allowed';
$_['error_export_origin_country_length'] = 'Origin country code should be 2 characters';
$_['error_export_value_no_positive_number'] = 'Export value must be a positive number (use dot as decimal seperator)';
$_['error_export_hsc_too_long'] = 'Harmonized System Code must be at most 8 characters long';
$_['error_could_not_retrieve_labels'] = 'Could not retrieve%s labels of order %s';
$_['error_product_weight_too_low'] = 'The weight of %s (row %d) is too low';
$_['error_hsc_missing'] = '%s (row %d) has no HS Code';
$_['error_origin_country_missing'] = '%s (row %d) has no Origin Country Code';

//DPD Settings
$_['environment'] = 'Environment';
$_['live'] = 'Live';
$_['demo'] = 'Demo';
$_['select_environment'] = 'Select Environment';
$_['include_return_label'] = 'Include return label';
$_['url'] = 'URL';
$_['username'] = 'Username';
$_['password'] = 'Password';
$_['sending_depot'] = 'Sending Depot';
$_['account_type'] = 'DPD Account Type';
$_['b2b'] = 'B2B';
$_['b2c'] = 'B2C';
$_['select_account_type'] = 'Please select a DPD account type';
$_['asynchronous'] = 'Asynchronous';
$_['asynchronous_help'] = 'Generates labels in the background while you work on something else';
$_['asynchronous_from'] = 'Asynchronous From';
$_['weight_default'] = 'Default Weight (Kg)';
$_['sending_address'] = 'Sending Address';
$_['company_name'] = 'Company Name';
$_['street_housenumber'] = 'Street + Housenumber';
$_['postal_code'] = 'Postal Code';
$_['place'] = 'Place';
$_['country_code'] = 'Country';
$_['sender_phone'] = 'Phone';
$_['consignor_eori_number'] = 'Eori Number';
$_['consignor_eori_number_help'] = 'Only required for export';
$_['account_settings'] = 'Account Settings';
$_['paper_format'] = 'Paper format';
$_['select_paper_format'] = 'Select a paper format';
$_['a4'] = 'A4';
$_['a6'] = 'A6';
$_['export_title'] = 'Export';
$_['export_hsc_source'] =  'Harmonized System Code Source';
$_['export_hsc_default'] =  'Default Harmonized System Code';
$_['export_value_source'] = 'Source of Customs Value';
$_['export_origin_country_source'] = 'Source of Origin Country Code';
$_['export_origin_country_default'] = 'Default Origin Country Code';
$_['export_vat_number_source'] = 'Source of Consignee VAT Number';
$_['use_dpd_product_data'] = 'Use DPD Shipping Data';
$_['delis_id'] = 'Delis ID';
$_['delis_password'] = 'Delis Password';

$_['tab_general'] = 'General';
$_['tab_advanced'] = 'Advanced';
$_['button_confirm']   = 'Confirm';
$_['button_dpd_return_label'] = 'DPD Return Label';
$_['button_dpd_shipping_label'] = 'DPD Shipping Label';
$_['button_dpd_generate_shipping_list'] = 'DPD Shipping List';
$_['button_dpd_edit_product_data'] = 'DPD Edit Shipping Data';
$_['menu_option_batches'] = 'DPD Labels';

$_['column_batch_id'] = 'Batch nr';
$_['column_batch_started'] = 'Started';
$_['column_batch_orders'] = 'Orders';
$_['column_batch_parcelnumbers'] = 'Parcel numbers';
$_['column_batch_status'] = 'Status';
$_['column_action'] = 'Actions';
$_['column_order_id'] = 'Order ID';
$_['column_shipment_status'] = 'Status';
$_['column_shipment_created'] = 'Created';
$_['column_is_return'] = 'Return';
