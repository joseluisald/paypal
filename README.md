# JOSELUISALD | AB

Esta biblioteca fornece a integração
com o PayPal

Com está biblioteca você pode
facilmente salvar um cartão para
pagar depois usando um token,
pode fazer um Checkout

## Instalação

Esta biblioteca está disponível através do composer,
como você pode ver pelo seu
[packagist page](https://packagist.org/packages/joseluisald/paypal).

```
composer require joseluisald/paypal
```

## Crie as constantes com os dados da sua conta

``` php

define('API', [
    'Type' => 'Sandbox',
    'ClientId' => '---------------',
    'ClientSecret' => '----------'
]);

```

## Instanciando a Classe Payment

Primeiramente instancie a classe passando
o Tipo (Sandbox ou Live), o ClientId e o ClientSecret.
Obs*. Os 3 campos são obrigatórios

``` php

use joseluisald\Payment\Payment;

$payment = new Payment(API['Type'], API['ClientId'], API['ClientSecret']);

```

## Método para pegar o ClientID, caso necessário para usar no formulário via JavaScript

``` php

$clientToken = $payment->getToken();

```

## Método para Salvar um cartão e ter o seu Id para um pagamento futuro

``` php

$data = '{
    "payment_source": {
        "card": {
            "number": "4111111111111111",
            "expiry": "2027-02",
            "name": "Luis",
            "billing_address": {
                "address_line_1": "Maciel",
                "address_line_2": "123",
                "admin_area_1": "RS",
                "admin_area_2": "Pelotas",
                "postal_code": "98000123",
                "country_code": "BR"
            },
            "experience_context": {
                "brand_name": "brand_name",
                "locale": "en-US",
                "return_url": "https://example.com/returnUrl",
                "cancel_url": "https://example.com/cancelUrl"
            }
        }
    }
}';

$idCard = $payment->saveCard($data);
$paymentToken = $payment->paymentToken($idCard);

$idCard = $paymentToken->id; // q5734p4c
$customeId = $paymentToken->customer->id;

```

## Método para criar uma order usando o token do cartão salvo

'vault_id' é referente ao '$idCard'

``` php

$reference_id = uniqid() . '_' . strtotime("now") . '_' . uniqid();
$data =
'{
    "intent": "CAPTURE",
    "purchase_units": [
        {
            "reference_id": "'.$reference_id.'",
            "amount": {
                "currency_code": "USD",
                "value": "100.00"
            }
        }
    ],
    "payment_source": {
        "card": {
              "vault_id": "q5734p4c"
        }
    }
}';

$createOrder = $payment->createOrder($data);

$idPayment = $createOrder->id;
$status = $createOrder->status;

```