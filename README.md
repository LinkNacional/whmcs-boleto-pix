# WHMCS - Módulo de PIX e Boleto PagHiper

Compatível com PHP 7.4, PHP 8.1 e WHMCS 8. Saiba mais em: [https://www.linknacional.com.br/whmcs/gateways/pix/paghiper/](https://www.linknacional.com.br/whmcs/gateways/pix/paghiper/)

* Seu boleto sai direto no PDF anexo a fatura (funcionalidade exclusiva).
* Boletos registrados conforme especificação da FEBRABAN. Baixa de pagamentos automática.
* Emissão de PIX de forma simplificada (com retorno automático).
* Emita boletos bancários direto do seu WHMCS.
##
* [Funcionalidades](#funcionalidades)
* [Como instalar](#instalação)
* [Como configurar](#configuração)
* [Solicitação de suporte e novas funcionalidades](#solicitação-de-suporte-e-novas-funcionalidades)

## Funcionalidades
### Funcionalidades gerais para boleto e PIX
* **Taxa percentual**: porcertagem aplicada ao valor da fatura por usar o PagHiper.
* **Taxa fixa**: valor, em reais, somado ao valor da fatura por usar o PagHiper.
* **Exibe ou não a frase fixa**: configurada no painel, no site do PagHiper
* **Valor máximo para pagamento**: valor máximo para realizar pagamento por boleto/PIX.
* **Valor mínimo para pagamento**: valor mínimo para realizar pagamento por boleto/PIX.
* **Isentar de taxas**: não aplicar taxas (Taxa percentual + Taxa fixa) caso o valor da fatura seja maior ou igual ao definido.
### Funcionalidades para PIX
* **Desconto por pagto. realizado via PIX**: percentual retirado do valor da fatura, caso o pagamento ocorra por PIX.
#### Desconto por critério
* **Critério do desconto**: quando aplicar o desconto acima? Atualmente, o módulo suporta o critério "Apenas para novos serviços".
* **Porcentagem do desconto por critério**: percentual retirado do valor da fatura caso o critério acima se for atendido.
### Funcionalidades para boleto
* **Abrir boleto ao abrir fatura**: gera o boleto e o abre automaticamente quando a página de fatura é acessada.
* **Tolerância para pagto**: número máximo de dias em que o boleto poderá ser pago após o vencimento.
* **Vencimento padrão para boletos emitidos**: escolha a quantidade de dias para o vencimento de boletos reemitidos (para faturas ja vencidas).
* **Percentual da multa por atraso (%)**: o percentual máximo autorizado é de 2%, de acordo artigo 52, parágrafo primeiro do Código de Defesa do Consumidor, Lei 8.078/90.
* **Juros proporcional**: aplica 1% de juros máximo ao mês, esse percentual será cobrado proporcionalmente aos dias de atraso. Dividindo 1% por 30 dias = 0,033% por dia de atraso.
#### **Desconto por pagamento antecipado**
* **Qtde. de dias para aplicação de desconto**: número de dias em que o pagamento pode ser realizado com antecedência recebendo o desconto extra.
* **Desconto por pagto. antecipado**: percentual do desconto que será aplicado caso o pagamento ocorra de forma antecipada.
* **Gerar boletos para todos os pedidos?**: automaticamente gera boletos para todos os pedidos criados, independentemente do gateway de pagamento selecionado.
* **Validar campos de CPF/CNPJ no checkout?**: fornece opções para validar ou o CPF e o CNPJ, ou apenas um dos dois, ou nenhum.

## Funcionalidades avançadas

### Anexar PDF do boleto/PIX ao PDF da fatura
Copie e cole o código abaixo no início do arquivo `invoicepdf.tpl` do seu tema.

```php
<?php include dirname(__FILE__).'/../../modules/gateways/paghiper/inc/helpers/attach_pdf_slip.php'; ?>
```

### inserir código PIX e linha digitável de boletos a e-mails

Para essa funcionalidade, edite seu template de e-mail `no menu superior, vá com o mouse sobre a chave de fenda > clique em Opções > Pesquise por "modelos > Procure e abra o modelo que deseja implementar a funcionalidade"`.

Você pode usar dois campos de mesclagens, um para boleto e outro para PIX, respectivamente: `{$linha_digitavel}` e `{$codigo_pix}`. Basta inserí-los nos templates de comunicação de e-mail nos locais desejados.

## Como instalar e configurar
### Instalação

1. Crie sua conta na PagHiper: [clique aqui para saber como criar](https://www.paghiper.com/duvidas/como-se-cadastrar-no-paghiper/).

2. Baixe a última versão do gateway, [clicando aqui](https://github.com/LinkNacional/whmcs-boleto-pix/releases/latest/download/whmcs-boleto-pix.zip).

3. Extraia o conteúdo do .zip baixado e faça upload das pastas `includes` e `modules` para a raíz da sua instalação do WHMCS.

4. Dentro da área administrativa do seu WHMCS: `no menu superior, vá com o mouse sobre a chave de fenda > clique em Apps & Integrations > Pesquise por "PagHiper"`
Após alguns segundos, as opções para `PagHiper Boleto` e `PagHiper PIX` irão aparecer. Clique sobre elas para ativá-las.

### Configuração

5. Para configurar, `passe o mouse sobre a chave de fenda > Clique em "Opções" > Pesquise por "gateways" > Procure pelo módulo PagHiper` que você ativou e clique sobre ele para configurá-lo.

6. **Preencha os campos essenciais para o funcionamento do gateway**: email, API key, token e ID do custom field contendo CPF/CNPJ.

## Solicitação de suporte e novas funcionalidades

Faça a solicitação [clicando aqui](https://github.com/LinkNacional/whmcs-boleto-pix/issues/new) para abrir uma nova issue.

Adicione um título, resumindo o problema e descreve o problema, se possível detalhando com erros e com prints.

# Licença

Copyright 2016-2019 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
