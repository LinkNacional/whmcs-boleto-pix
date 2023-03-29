<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once dirname(__FILE__) . '/../../modules/gateways/paghiper/inc/helpers/gateway_functions.php';

function paghiper_getClientDetails($vars, $gateway_config)
{
    $gateway_admin = $gateway_config['admin'];
    $backup_admin = array_shift(mysql_fetch_array(mysql_query('SELECT username FROM tbladmins LIMIT 1')));

    $whmcs_admin = paghiper_autoSelectAdminUser($gateway_config);

    $query_params = [
        'clientid' => $vars['userid'],
        'stats' => false
    ];

    return localAPI('getClientsDetails', $query_params, $whmcs_admin);
}

function paghiper_getClientCustomFields($vars, $gateway_config)
{
    $clientCustomFields = [];

    if (array_key_exists('custtype', $vars) && $vars['custtype'] == 'existing') {
        $client_details = paghiper_getClientDetails($vars, $gateway_config);

        foreach ($client_details['customfields'] as $key => $value) {
            $clientCustomFields[$value['id']] = $value['value'];
        }
    } else {
        foreach ($vars['customfield'] as $key => $value) {
            $clientCustomFields[$key] = $value;
        }
    }

    if (count($taxIdFields) > 1) {
        $clientTaxIds[] = $clientCustomFields[$taxIdFields[0]];
        $clientTaxIds[] = $clientCustomFields[$taxIdFields[1]];
    } else {
        $clientTaxIds[] = $clientCustomFields[$taxIdFields[0]];
    }

    $isValidTaxId = false;
    foreach ($clientTaxIds as $clientTaxId) {
        if (paghiper_is_tax_id_valid($clientTaxId)) {
            $isValidTaxId = true;
            break ;
        }
    }

    if (!$isValidTaxId) {
        if (array_key_exists('custtype', $vars) && $vars['custtype'] == 'existing') {
            return ['CPF/CNPJ inválido! Cheque seu cadastro.'];
        } else {
            return ['CPF/CNPJ inválido!'];
        }
    }
}

//add_hook("ClientDetailsValidation", 1, "paghiper_clientValidateTaxId");
add_hook('ShoppingCartValidateCheckout', 1, 'paghiper_clientValidateTaxId');
