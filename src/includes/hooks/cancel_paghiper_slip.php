<?php

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * @since 2.5.3
 *
 * @param array $vars
 *
 * @return void
 */
function paghiper_cancel_slip_or_pix($vars)
{
    $invoiceId = $vars['invoiceid'];
    $enabledGateways = array_keys(getGatewaysArray());

    logTransaction('paghiper_pix', $enabledGateways, 'debug');

    if (
        !in_array('paghiper', $enabledGateways)
        && !in_array('paghiper_pix', $enabledGateways)
    ) {
        return;
    }

    $transactions = Capsule::table('mod_paghiper')
        ->where('order_id', $invoiceId)
        ->where('status', 'pending')
        ->get(['transaction_id as id', 'transaction_type as type'])
        ->toArray();

    if (count($transactions) === 0) {
        return;
    }

    $gatewayCode = in_array('paghiper', getGatewaysArray()) ? 'paghiper' : 'paghiper_pix';

    $gatewayVars = getGatewayVariables($gatewayCode);

    $requestBody = [
        'apiKey' => $gatewayVars['api_key'],
        'token' => $gatewayVars['token'],
        'status' => 'canceled',
        'transaction_id' => '', // Defined below in the foreach loop.
    ];

    $requestHeaders = [
        'Accept: application/json',
        'Accept-Encoding: application/json',
        'Content-Type: application/json; charset=UTF-8'
    ];

    $curlRequest = curl_init();

    curl_setopt_array($curlRequest, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_HTTPHEADER => $requestHeaders,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    foreach ($transactions as $transaction) {
        $gatewayLabel = $transaction->type === 'billet' ? 'boleto' : 'pix';
        $gatewayCode = $transaction->type === 'billet' ? 'paghiper' : 'paghiper_pix';

        $requestBody['transaction_id'] = $transaction->id;
        $requestUrl = $transaction->type === 'billet'
            ? 'https://api.paghiper.com/transaction/cancel/'
            : 'https://pix.paghiper.com/invoice/cancel/';

        curl_setopt($curlRequest, CURLOPT_URL, $requestUrl);
        curl_setopt($curlRequest, CURLOPT_POSTFIELDS, json_encode($requestBody));

        $response = json_decode(curl_exec($curlRequest), true);
        $httpCode = curl_getinfo($curlRequest, CURLINFO_HTTP_CODE);

        if ($httpCode === 201) {
            paghiper_log_status_to_db('canceled', $transaction->id);
            logTransaction(
                $gatewayCode,
                ['post' => $requestBody, 'json' => $response, 'url' => $requestUrl],
                ucfirst($gatewayLabel) . " adicional cancelado com sucesso. Transação #{$transaction->id}"
            );
        } else {
            paghiper_log_status_to_db('force_canceled', $transaction->id);
            logTransaction(
                $gatewayCode,
                ['post' => $requestBody, 'json' => $response, 'url' => $requestUrl],
                "Não foi possível cancelar o $gatewayLabel"
            );
        }
    }

    curl_close($curlRequest);
}

add_hook('InvoiceCancelled', 1, 'paghiper_cancel_slip_or_pix');
add_hook('InvoicePaid', 1, 'paghiper_cancel_slip_or_pix');
