<?php

use setasign\Fpdi;
use WHMCS\Database\Capsule;

require_once __DIR__ . '/gateway_functions.php';

$result = (array) Capsule::table('tblinvoices')
    ->where('id', $invoiceid)
    ->first(['paymentmethod', 'total', 'status']);

logModuleCall('lknhooknotification', 'result', [$result], []);

if (empty($result)) {
    return;
}

if (in_array($result['status'], ['Paid', 'Draft', 'Cancelled', 'Refunded'], true)) {
    return;
}

$invoice_total = $result['total'];
$payment_method_slug = $result['paymentmethod'];

if ($payment_method_slug !== 'paghiper' && $payment_method_slug !== 'paghiper_pix') {
    $gateway_variables = getGatewayVariables('paghiper');
    $issue_all_config = (int) $gateway_variables['issue_all'];
    $must_issue = ($issue_all_config === 1 || $issue_all_config === 0) ? $issue_all_config : 1;

    if (!$must_issue) {
        return;
    }
}

$isElegibleForPayment = paghiper_is_balance_elegible_for_payment($payment_method_slug, $invoiceid);

if (is_string($isElegibleForPayment)) {
    return;
}

$is_pix = ($payment_method_slug == 'paghiper_pix');

$whmcs_url = rtrim(\App::getSystemUrl(), '/');
$assets_url = "{$whmcs_url}/modules/gateways/paghiper/assets/img";
$json_url = "{$whmcs_url}/modules/gateways/";
$json_url .= ($is_pix) ? 'paghiper_pix.php' : 'paghiper.php';
$json_url .= '?invoiceid=' . $invoiceid . '&uuid=' . $clientsdetails['userid'] . '&mail=' . $clientsdetails['email'] . '&json=1';

$invoice_url = str_replace('&json=1', '', $json_url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $json_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$json = curl_exec($ch);
$result = json_decode($json);

$transaction_id = (isset($result->transaction_id)) ? $result->transaction_id : '';
$asset_url = (!$is_pix) ?
    ((property_exists($result, 'bank_slip') && !is_null($result->bank_slip)) ? $result->bank_slip->url_slip_pdf : $result->url_slip_pdf) :
    ((property_exists($result, 'pix_code') && !is_null($result->pix_code)) ? $result->pix_code->qrcode_image_url : $result->qrcode_image_url);

if ((in_array($status, ['Unpaid', 'Payment Pending'])) && (isset($asset_url) && !empty($asset_url)) && (isset($transaction_id) && !empty($transaction_id))) {
    $basedir = (function_exists('dirname')) ? dirname(__DIR__, 2) : realpath(__DIR__ . '/../..');
    $assetdir = $basedir . '/tmp/' . ((!$is_pix) ? 'billets' : 'pix');
    $filename = $assetdir . '/' . $transaction_id . ((!$is_pix) ? '.pdf' : '.png');

    $print_paghiper_page = false;

    // Checamos se temos um boleto para disponibilizar
    if (file_exists($filename)) {
        $print_paghiper_page = true;
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $asset_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $asset_url);

        $rawdata = curl_exec($ch);
        $pdf_transaction = file_put_contents($filename, $rawdata);

        if ($pdf_transaction) {
            $print_paghiper_page = true;
        }
    }

    // If file doesn't exist and cannot be written, don't waste efforts. Just send the default PDF
    if ($print_paghiper_page) {
        // Primeiro checamos se a transação não se trata um PIX
        if (!$is_pix) {
            /* Bloco inicializador do boleto */
            require_once $basedir . '/inc/fpdi/autoload.php';
            require_once $basedir . '/inc/fpdi/TcpdfFpdi.php';
            $pdf = new Fpdi\TcpdfFpdi();

            // TODO: Implementar header e footer aqui
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);

            $pdf->AddPage();

            $pdf->setSourceFile($filename);
            $tplIdx = $pdf->importPage(1);

            $pdf->useTemplate($tplIdx, 0, 0, 210);

            /* Bloco inicializador do template comum */
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);

        // Caso não seja, tentamos printar os dados do PIX
        } else {
            $emv = ($result->pix_code) ? $result->pix_code->emv : $result->emv;
            $pix_url = ($result->pix_code) ? $result->pix_code->pix_url : $result->pix_url;
            $bacen_url = ($result->pix_code) ? $result->pix_code->bacen_url : $result->bacen_url;

            $pdf->Image($whmcs_url . '/modules/gateways/paghiper/assets/img/pix.jpg', 10, 10, 25, '', 'JPEG');

            $pdf->SetXY(38, 8);

            // Set font
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->Cell(30, 20, 'Pague sua fatura usando PIX!', 0, 'C');

            // Instruções
            $pdf->SetXY(20, 35);
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->Ln(10);
            $pdf->SetX(60);
            $pdf->Image("{$assets_url}/ico_1-app.png", 30, 35, 30, '', 'PNG');
            $pdf->Multicell(120, 0, 'Abra o app do seu banco ou instituição financeira e entre no ambiente Pix.', 0, 'L');
            $pdf->Ln(20);
            $pdf->SetX(60);
            $pdf->Image("{$assets_url}/ico_2-qr.png", 30, 65, 30, '', 'PNG');
            $pdf->Multicell(120, 0, 'Escolha a opção Pagar com QR Code e escanele o código abaixo.', 0, 'L');
            $pdf->Ln(20);
            $pdf->SetX(60);
            $pdf->StartTransform();
            // set clipping mask
            $pdf->Circle(45, 109, 10, 0, 360, 'CNZ');
            $pdf->Image("{$assets_url}/ico_3-ok.png", 35, 99, 20, '', 'PNG');
            $pdf->StopTransform();
            $pdf->Multicell(120, 0, 'Confirme as informações e finalize o pagamento.', 0, 'L');

            $pdf->Image($filename, 0, 130, 0, 0, 'PNG', false, 'C', false, 300, 'C', false, false, 0, false, false, false);

            $pdf->SetY(215);
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->Multicell(0, 10, "Fatura #{$invoiceid} - R$ " . number_format($invoice_total, 2, ',', '.'), $border = 0, $align = 'C');

            $pdf->SetY(234);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->Multicell(0, 10, 'Você também pode pagar usando PIX copia e cola:', $border = 0, $align = 'C');

            $pdf->SetFont('dejavusans', '', 8);
            $pdf->SetY(240);
            $html = '<form method="post" action="' . $invoice_url . '" enctype="multipart/form-data">
            <textarea cols="100" rows="3" name="text">' . $emv . '</textarea><br />
            </form>';
            $pdf->writeHTML($html, true, 0, true, 0);

            $pdf->SetY(260);
            $pdf->Multicell(0, 10, 'Após o pagamento, podemos levar alguns segundos para confirmar o seu pagamento.
            Você será avisado assim que isso ocorrer!', $border = 0, $align = 'C');
        }

        $pdf->AddPage();
    }
}

// Uncomment for debugging
/*header("Content-type: application/pdf");
$pdf->Output('name.pdf', 'I');
exit();*/
