---
title: CASHLINK Payment API

toc_footers:
- <a href="index.html">Go to Home</a>
- <a href="platform.html">Go to Platform API</a>

search: true
---
# Payment API

With the CASHLINK Payment API you can automate creating payment links for invoices, subscriptions and products. You can also receive notification when one of your links has been paid via webhooks.

Payment links have a unique, randomly-generated URL, for example `https://cashlink.de/business/+VX0GK5cvM1dO`. Share this URL with your customers on your website, via email, etc.  Use `whatsapp_url` when sending the link via WhatsApp. Using the default link will lead to issues with line breaks making it impossible to click the link.

## Test IBAN

You can test your payment integration with the test IBAN `DE89370400440532013000`. Payments from this IBAN will immediately succeed. Please note that with non-test IBANs, payments may take longer to process, so do not rely on real payments being confirmed immediately.

## Invoice links

> Example: Create invoice payment link

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: USERKEY'
  --data '{
    "type": "invoice",
    "amount": "12.34",
    "subject": "Invoice no. 12345"
  }'
  https://cashlink.de/business/api/v1/cashlink
```

> Response:

```json
{
  "public_id": "VX0GK5cvM1dO",
  "url": "https://cashlink.de/business/+VX0GK5cvM1dO",
  "whatsapp_url": "https://cashlink.de/business/l/VX0GK5cvM1dO"
}
```

[Example invoice payment page](https://cashlink.de/business/demolink)

Invoice links can only be paid once. They are usually used to offer online payment for a single order.

## Product links

> Example: Create product link

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: USERKEY'
  --data '{
    "type": "product",
    "amount": "12.34",
    "subject": "Online Payments EBook"
  }'
  https://cashlink.de/business/api/v1/cashlink
```

[Example product payment page](https://cashlink.de/business/+FaHc8AKUGnmP)

Product links are like invoice links but can be paid more than once. Use these to receive payment for products that you can sell many times.

To make a purchase, payers must enter their full name and email address. You will receive this additional information in the `cashlink.paid` webhook. See [`cashlink.paid` event](#code-cashlink-paid-code-new-payment) for details.

## Recurring payments

> Example: Create recurring payment link

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: USERKEY'
  --data '{
    "type": "subscription",
    "subscription_plan": "monthly"
    "amount": "12.34",
    "subject": "Monthly plan",
  }'
  https://cashlink.de/business/api/v1/cashlink
```

[Example recurring payment page](https://cashlink.de/business/+g81C9EqWVqd8)

Recurring payment links may be paid multiple times by different customers. Each time a recurring payment link is paid, the customer starts a subscription to pay the link's amount every interval (e.g. monthly).

To start a subscription, payers must enter their full name and email address. You will receive this additional information in the `cashlink.paid` webhook. See [`cashlink.paid` event](#code-cashlink-paid-code-new-payment) for details.

Recurring payments are restricted to the credit card and direct debit payment methods.

Use the `subscription_plan` field to specify the payment interval. Choices:

- `monthly`: Charge on the day someone signs up for the subscription, and then automatically charge every 4 weeks.
  Example: User signs up on 2017/11/05, then the first charge will be on 2017/11/05, and next charge will be on 2017/12/05, etc.
  If signup day of month is > 28, then all future charges will be made on the 28th of the following months.

(Contact CASHLINK if you need other payment intervals.)

## Pre-allocated URLs

> Example: Pre-allocate payment link URL

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: USERKEY'
  --data '{}'
  https://cashlink.de/business/api/v1/cashlink-public-id
```

> Response:

```json
{
  "signature": "VX0GK5cvM1dO:PygAVDcv2hFi4RqLxK3p0vs58r8",
  "public_id": "VX0GK5cvM1dO",
  "url": "https://cashlink.de/business/+VX0GK5cvM1dO",
  "whatsapp_url": "https://cashlink.de/business/l/VX0GK5cvM1dO"
}
```

You can retrieve a payment link `public_id`/URL before actually having created the payment link with subject and amount. This can be useful if you can only know the payment link's subject or amount after stuffing the URL into some third-party system.

To pre-allocate a payment link URL, use the following API call to retrieve a signed `public_id`/URL:

Note that at the point of this API response, the payment link hasn't actually been created yet, so the URL is inaccessible.


> Example: Create a payment link with a pre-allocated URL

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: USERKEY'
  --data '{
    "public_id": "VX0GK5cvM1dO:PygAVDcv2hFi4RqLxK3p0vs58r8",
    "amount": "12.34",
    "subject": "Invoice no. 12345"
  }'
  https://cashlink.de/business/api/v1/cashlink
```

To create a payment link with a pre-allocated URL, in the create payment link request, set the `public_id` field to the `signature` value from the pre-allocation response.


# Payment notification

## `cashlink.paid` – New payment

> Example: `cashlink.paid` event data

```json
{
  "event": "cashlink.paid",
  "timestamp": "2017-10-09T13:11:03.661620",
  "business": "VX0GK5cvM1dO",
  "data": {
    "is_test": false,
    "public_id": "wxnfqAVVPWwZ",
    "cashlink": {
      "type": "invoice",
      "public_id": "VX0GK5cvM1dO",
      "subject": "Invoice no. 12345",
      "amount": "12.34",
      "amount_currency": "EUR"
    },
    "payer": {
      "first_name": "Jane",
      "last_name": "Doe",
      "email": "jane@doe.com"
    }
  }
}
```

Sent when a payment has been successfully processed.

Test IBAN payments will have `is_test` set to true.

The `payer` field is available for product links and recurring payments.

### Recurring payments

For recurring payments, `cashlink.type` will be `"subscription"`, and the following extra fields will be available:

- `data.cashlink.next_charge`: scheduled next charge (`YYYY-MM-DDTHH:MM:SS.micro`)
- `data.recurring_initial`: `true` if this was the initial payment of a subscription
