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
$_['locale'] = 'nl_NL';
$_['dpdbenelux_disabled_message'] = 'DPD verzendmethodes zijn uitgeschakeld. De huidige configuratie wordt genegeerd. Schakel DPD verzending in door de status te wijzigen naar Ingeschakeld';
$_['title'] = 'Title';
$_['entry_title'] = 'DPD Parcelservice';

$_['description'] = 'Omschrijving';
$_['entry_description'] = 'Verzending bij u thuis';

// Heading
$_['heading_title']    = 'DPD Parcelservice';
$_['batches_heading_title'] = 'DPD Labels';
$_['shipments_heading_title'] = 'DPD zendingen';
$_['product_data_heading_title'] = 'DPD exportgegevens';

// Text
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: U heeft succesvol de DPD Parcelservice configuratie gewijzigd!';
$_['text_edit']        = 'Wijzig DPD Parcelservice';
$_['text_enabled'] = 'Ingeschakeld';
$_['text_disabled'] = 'Uitgeschakeld';
$_['text_all_zones'] = 'Alle zones';
$_['text_none'] = '--- Geen --';
$_['text_number_of_parcels']   = 'Aantal pakketten';
$_['text_pixels'] = 'Pixels';
$_['text_percentage'] = 'Procent';
$_['text_generate'] = 'Genereer';
$_['text_labels_queued'] = 'Labels in de wachtrij geplaatst voor';
$_['text_labels_generated'] = 'Labels gegenereerd voor';
$_['text_shipments'] = 'zendingen';
$_['text_parcels'] = 'Pakketten';
$_['text_status'] = 'Status';
$_['text_labels_not_yet_generated'] = 'Er zijn nog geen labels gegenereerd';
$_['text_status_request'] = 'Aanvraag';
$_['text_status_queued'] = 'Wacht';
$_['text_status_processing'] = 'Bezig';
$_['text_status_failed'] = 'Mislukt';
$_['text_status_partially_failed'] = 'Gedeeltelijk mislukt';
$_['text_status_success'] = 'Succes';
$_['text_batch_list'] = 'Batches';
$_['text_shipment_list'] = 'Zendingen';
$_['text_both'] = 'Beide';
$_['text_shipment_history'] = 'Historie';
$_['text_data_only_required_for_export'] = 'Deze gegevens zijn alleen nodig voor export buiten de Europese interne markt.';
$_['title_shipment_history'] = 'Historie van DPD Zendingen';
$_['text_return'] = 'retour';
$_['text_using_timezone'] = 'Gebruikte tijdzone';

// Entry
$_['entry_cost']       = 'Prijs';
$_['entry_tax_class']  = 'BTW Groep';
$_['entry_geo_zone']   = 'Geo Zone';
$_['entry_status']     = 'Status';
$_['entry_sort_order'] = 'Volgorde';
$_['entry_google_maps_api_key'] = 'Google Maps API key';
$_['entry_google_maps_width'] = 'Map breedte';
$_['entry_google_maps_height'] = 'Map hoogte in pixels';
$_['entry_number_of_shops'] = 'Max aantal parcelshops weergegeven';
$_['entry_show_from_day'] = 'Laat zien vanaf dag';
$_['entry_show_from_time'] = 'Laat zien vanaf tijd';
$_['entry_show_till_day'] = 'Laat zien tot dag';
$_['entry_show_till_time'] = 'Laat zien tot tijd';
$_['entry_select_day'] = 'Selecteer een dag';
$_['entry_days_monday'] = 'Maandag';
$_['entry_days_tuesday'] = 'Dinsdag';
$_['entry_days_wednesday'] = 'Woensdag';
$_['entry_days_thursday'] = 'Donderdag';
$_['entry_days_friday'] = 'Vrijdag';
$_['entry_days_saturday'] = 'Zaterdag';
$_['entry_days_sunday'] = 'Zondag';
$_['entry_batch_id'] = 'Batchnr';
$_['entry_started'] = 'Gestart';
$_['entry_order_id'] = 'Order ID';
$_['entry_is_return'] = 'Retour';
$_['entry_hsc'] =  'Harmonized System Codes';
$_['entry_value'] = 'Waarde voor de douane';
$_['entry_origin_country'] = 'Landcode herkomst';
$_['entry_vat_number'] = 'BTW nummer Geadresseerde';

$_['entry_scp_ncp_choice'] = 'SCP/NCP keuze';
$_['general_status']   = 'DPD Parcelservice status';

// Error
$_['errors'] = 'Fouten';
$_['error_permission'] = 'Waarschuwing: U heeft onvoldoende rechten om DPD Parcelservice te wijzigen!';
$_['error_not_filled_in'] = 'Voer aub alle verplichte velden in';
$_['error_login'] = 'De combinatie van Delis ID en Wachtwoord is ongeldig/onjuist';
$_['error_no_orders_selected'] = 'Selecteer een bestelling om te printen';
$_['error_no_soap_installed'] = 'U heeft de SOAP extensie niet geïnstalleerd/geactiviteerd, deze is vereist voor de DPD extensie. Neem contact op met uw hosting provider.';
$_['error_asynchronous_from_not_numeric'] = 'Vul aub een aantal in voor Asynchroon Vanaf';
$_['error_no_labels'] = 'Er zijn geen labels';
$_['error_generating_labels'] = 'Fouten bij het genereren van de labels:';
$_['error_payment_zipcode_required'] = 'Betaling postcode is vereist voor parcelshop levering';
$_['error_shipping_zipcode_required'] = 'Verzending zipcode is vereist';
$_['error_customs_only_once'] = 'Voor verzendingen met douanegegevens kunnen maar één keer labels worden gegenereerd';
$_['error_customs_multiple_parcels'] = 'Voor verzendingen met douanegegevens is maar 1 pakket toegestaan';
$_['error_export_origin_country_length'] = 'Code land van herkomst moet 2 karakters bevatten';
$_['error_export_value_no_positive_number'] = 'Export waarde moet een positief nummer zijn (gebruik punt voor decimaalscheiding)';
$_['error_export_hsc_too_long'] = 'Harmonized System Code mag niet langer zijn dan 8 karakters';
$_['error_could_not_retrieve_labels'] = 'Kon%s labels van order %s niet ophalen';
$_['error_product_weight_too_low'] = 'Het gewicht van %s (rij %d) is te laag';
$_['error_hsc_missing'] = '%s (row %d) heeft geen HS Code';
$_['error_origin_country_missing'] = '%s (row %d) heeft geen landcode herkomst';


//DPD Settings
$_['environment'] = 'Omgeving';
$_['live'] = 'Live';
$_['demo'] = 'Demo';
$_['select_environment'] = 'Selecteer een omgeving';
$_['include_return_label'] = 'Standaard Retour Label bijvoegen';
$_['url'] = 'URL';
$_['username'] = 'Gebruikersnaam';
$_['password'] = 'Wachtwoord';
$_['sending_depot'] = 'Verzenddepot';
$_['account_type'] = 'DPD Account Type';
$_['b2b'] = 'B2B';
$_['b2c'] = 'B2C';
$_['select_account_type'] = 'Selecteer een account type';
$_['asynchronous'] = 'Asynchroon';
$_['asynchronous_help'] = 'Genereert labels op de achtergrond terwijl u aan iets anders werkt';
$_['asynchronous_from'] = 'Asynchroon vanaf';
$_['weight_default'] = 'Standaard gewicht (Kg)';
$_['sending_address'] = 'Verzendadres';
$_['company_name'] = 'Bedrijfsnaam';
$_['street_housenumber'] = 'Straat + nummer';
$_['postal_code'] = 'Postcode';
$_['place'] = 'Plaats';
$_['country_code'] = 'Land';
$_['sender_phone'] = 'Telefoon';
$_['consignor_eori_number'] = 'Eori Nummer';
$_['consignor_eori_number_help'] = 'Alleen nodig voor export';
$_['account_settings'] = 'Account instellingen';
$_['paper_format'] = 'Papier formaat';
$_['select_paper_format'] = 'Selecteer een formaat';
$_['a4'] = 'A4';
$_['a6'] = 'A6';
$_['export_title'] = 'Export';
$_['export_hsc_source'] =  'Bron van Harmonized System Codes';
$_['export_hsc_default'] =  'Standaard Harmonized System Code';
$_['export_value_source'] = 'Bron van waarde voor de douane';
$_['export_origin_country_source'] = 'Bron van landcode herkomst';
$_['export_origin_country_default'] = 'Standaard landcode herkomst';
$_['export_vat_number_source'] = 'Bron van BTW nummer Geadresseerde';
$_['use_dpd_product_data'] = 'Gebruik DPD verzendgegevens';
$_['delis_id'] = 'Delis ID';
$_['delis_password'] = 'Delis Wachtwoord';

// Tabs, buttons
$_['tab_general'] = 'Algemeen';
$_['tab_advanced'] = 'Geavanceerd';
$_['button_confirm']   = 'Bevestigen';
$_['button_dpd_return_label'] = 'DPD retourlabel';
$_['button_dpd_shipping_label'] = 'DPD verzendLabel';
$_['button_dpd_generate_shipping_list'] = 'DPD verzendlijst';
$_['button_dpd_edit_product_data'] = 'DPD bewerk verzendgegevens';
$_['menu_option_batches'] = 'DPD Labels';

$_['column_batch_id'] = 'Batchnr';
$_['column_batch_started'] = 'Gestart';
$_['column_batch_orders'] = 'Orders';
$_['column_batch_parcelnumbers'] = 'Pakketnummers';
$_['column_batch_status'] = 'Status';
$_['column_action'] = 'Acties';
$_['column_order_id'] = 'Order ID';
$_['column_shipment_status'] = 'Status';
$_['column_shipment_created'] = 'Aangemaakt';
$_['column_is_return'] = 'Retour';
