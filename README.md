# WHMCS - Módulo de PIX e Boleto PagHiper

OBS: Este módulo segue com suporte e atualizações independentes do repositório oficial do PagHiper.

Emissão de PIX de forma simplificada ( com retorno automático ).

Emita boletos bancários direto do seu WHMCS.

Boletos registrados conforme especificação da FEBRABAN. Baixa de pagamentos automática.

Seu boleto sai direto no PDF anexo a fatura (funcionalidade exclusiva).

* **Versão mais Recente:** 2.5.0
* **Requisitos:** cURL e JSON ativado.
* **Compatibilidade:** WHMCS 8.X, PHP 7.x., PHP 8.1, Mod_rewrite opcional


# Como Instalar

1. Crie sua conta na PagHiper [clique aqui para saber como](https://github.com/paghiper/whmcs/wiki/Como-criar-seu-cadastro-na-PagHiper).

2. Baixe o gateway da **PagHiper**, extraia o conteúdo e faça upload das pastas includes e modules para a raíz da sua instalação do WHMCS

3. Dentro da área administrativa do seu WHMCS, vá em: Setup > Payments > Payment Gateways (em inglês) ou Opções > Pagamentos > Portais para Pagamento

4. Após, va na aba “All Payment Gateways” ou "Todos os Portais de Pagamento" e procure pelo modulo de nome: “PagHiper Boleto” e clique em cima.

5. Será exibida uma pagina semelhante a que se encontra na figura abaixo. Basta configurar com suas credenciais.

6. Repita o processo para a configuração do PIX:
Na aba “All Payment Gateways” ou "Todos os Portais de Pagamento" e procure pelo modulo de nome: “PagHiper PIX” e clique em cima.

7. Será exibida uma pagina semelhante a que se encontra na figura abaixo. Basta configurar com suas credenciais.

8. Adicione o texto abaixo no arquivo invoicepdf.tpl do seu tema, para anexar boletos e códigos PIX ao PDF das faturas (opcional)

```<?php include dirname(__FILE__).'/../../modules/gateways/paghiper/inc/helpers/attach_pdf_slip.php'; ?>```

9. Para inserir código PIX e linha digitável de boletos, edite seu template de e-mail em Opções (Setup) > Modelos de e-mail (E-mail templates). Você pode usar dois campos de mesclagens, um para boleto e outro para PIX, respectivamente: {$linha_digitavel} e {$codigo_pix}. Basta inserí-los nos templates de comunicação de e-mail nos locais desejados.

Se tiver dúvidas sobre esse processo, acesse nosso [guia de configuração de plugin](https://github.com/paghiper/whmcs/wiki/Configurando-o-plugin-no-seu-WHMCS)

# Licença

Copyright 2016-2019 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
