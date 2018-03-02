---
title: CASHLINK API documentation

toc_footers:
- <a href="payments.html">Go to Payment API</a>
- <a href="platform.html">Go to Platform API</a>

search: true
---

# CASHLINK API documentation

Parts of this documentation:

- This page: General information on API usage (authentication, versioning, error handling, webhooks)
- [Payments API](payments.html): Create payment links and receive payment notifications.
- [Platform API](platform.html): Create CASHLINK accounts on behalf of your platform's users to use with the Payments API.

An access key is required to get access to the API. To get an access key, please contact CASHLINK (info@cashlink.de). The access key is referred to as "USERKEY" in the Payment API documentation, and "APPKEY" in the Platform API documentation.

## Basics

CASHLINK provides a REST-style API (HTTPS + JSON). HTTP methods follow the REST semantics (GET to retrieve information, POST to create a new entity, PUT to add something to an existing entity, UPDATE to update an entity, DELETE to delete an entity) unless noted differently.

Base URL for all API calls is `https://cashlink.de/business/api/v1/`.

All requests must include the `X-Api-Key` header with an API key for authentication. (The "USERKEY" for creating payment links, and the "APPKEY" for creating new businesses as a Platform API user.)

## Versioning
The CASHLINK API is versioned; breaking API changes are always introduced with a version number increment. In order to ease migration, you may request that your API request be processed with the behaviour of an earlier API version, if the called endpoint supports this earlier version.

> Example: Force 2017-10-01 API version

```sh
curl
  -X POST
  -H "X-Api-Version: 2017-10-01"
  ...
  https://cashlink.de/business/api/v1/...
```

To request an earlier version, simply include the `X-Api-Version` header set to your desired version. If no header is passed, the latest stable API version is assumed. If the endpoint does not support your desired version anymore, it fails with a 400 Bad Request.

### Available API versions

Version    | Forward compatible to | Remarks
-----------|-----------------------|----------------------
2018-01-15 |                       | default version starting 2018/05/01
2017-10-01 | 2018-01-15            | default version until 2018/04/30

See the Changelog below for details on API versions.

## Error handling

> Example: Missing subject field

```json
{"subject": ["This field is required."]}
```

If the input you provided to one of the API calls is missing or does not validate, you will get a HTTP 4xx response, along with a JSON object response body that provides detailed information on the request errors. Errors are a list of error strings.

## Test Mode

By default your API key will be in "test mode." You can use test mode to safely develop and test your integration.  Differences between production mode and test mode:

- Payments: Payments will only be possible from a test IBAN and will not be charged. See [Payment API: Test Mode](/payments.html#test-mode) for details.
- Platform: Businesses created will not be sent to the PSP. See [Platform API: Test Mode](/platform.html#test-mode) for details.

Please get in contact when your integration is ready so we can switch your API key to production mode. We can also provide you with a separate production API key if that's easier for you.

## Events and Webhooks

> Example: Webhook request to your server for `cashlink.paid` event

```
POST /webhook/payments HTTP/1.1
Host: yourdomain.com
User-Agent: cashlink
Content-Type: application/json
Hook-Hmac: Pmwczrx6I1+KzPRYjMrB8nH3Hq[...]
X-Request-Id: 7518ca89-3eb1-4884-ac28-ff81531d2fd8
...
{
  "event": "cashlink.paid",
  "timestamp": "2017-10-09T13:11:03.661620",
  "business": "utqztAwTcQKB",
  "data": {
    "is_test": false,
    "public_id": "wxnfqAVVPWwZ",
    "cashlink": {
      "type": "invoice",
      "public_id": "VX0GK5cvM1dO",
      "subject": "Invoice no. 12345",
      "amount": "12.34",
      "amount_currency": "EUR"
    }
  }
}
```

Get in contact with CASHLINK (info@cashlink.de) if you want to use webhooks.

CASHLINK makes a JSON POST request to your subscription URL when an event you're subscribed to happens.

**Integration examples:** See [https://github.com/cashlink/apidoc/tree/master/examples](https://github.com/cashlink/apidoc/tree/master/examples)

**Timeouts:** Webhook requests have a timeout of **2 seconds** and are not retried.

**Platform API users:** The `business` field identifies the business this event happened to. This is useful if you have the events of multiple businesses sent to the same webhook endpoint; use the `business` field to know which business is concerned in this case.

### Security

You may optionally verify the authenticity of the request using the HMAC value provided in the *Hook-Hmac* header. A request is guaranteed to be sent from CASHLINK if the following holds true:

`HMAC-SHA512(secret_key, request body) == base64decode(request_headers["Hook-Hmac"])`

`secret_key` is a secret value provided to you upon registration.

Note that for optimal security you should use constant-time comparison instead of your programming language's default `==` operator in order to be protected from [timing attacks](https://en.wikipedia.org/wiki/Timing_attack).

## Changelog

### 2018-01-15

- The `type` field is now required and must be one of `"invoice"`, `"subscription"`, or `"product"`.
  This is forward compatible to version 2017-10-01: You can start using the `type` field today.
