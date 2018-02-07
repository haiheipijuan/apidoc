---
title: CASHLINK Platform API

toc_footers:
- <a href="index.html">Go to Home</a>
- <a href="payments.html">Go to Payment API</a>

search: true
---
# Platform API

> <sup>1</sup> Platform = an application that allows other businesses to sign up and receive payments from their customers.

The CASHLINK Platform API provides a way for platforms<sup>1</sup> to allow users (businesses) to receive payments from their customers.

The CASHLINK Platform API is very easy to integrate. You can start with a very lightweight integration that can be implemented in a few hour's work. A more heavyweight integration would also deal with providing business information (address, tax ID, and other legal information) upon registration, but this is entirely optional.

Platform API requests must be made with the platform's unique APPKEY.

Your platform receives the business' USERKEY upon registration that can be used to create payment links with the [Payment API](payments.html). Your platform can also receive payment notification webhooks for these businesses. The `business` field in a webhook request body identifies the business an event happened to. This is useful if you have the events of multiple businesses sent to the same webhook endpoint; use the `business` field to know which business is concerned in this case.

Business accounts registered through the Platform API are ready to receive payments immediately after registration. To receive payouts to their bank accounts, users will have to complete their legal information (see [Providing legal information](#providing-legal-information) below). **Note:** For legal reasons, in order to enable the direct debit payment method (one of the most-used payment methods in Germany), users will have to complete their legal information and upload a copy of their representative's passport on the CASHLINK website before receiving their first direct debit payment.

## Test Mode

In test mode any businesses created with the Platform API will not be created at our Payment Service Providers. Businesses registered in test mode can not receive real payments even after switching to production mode.  Payments will only be possible from a test IBAN and will not be charged. See [Payment API: Test Mode](/payments.html#test-mode) for details.

## Registering businesses


> Example: Create a CASHLINK account for one of your platform's users

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: XXXXXXXX'
  --data '{
    "email": "jane@doe.com",
    "business_name": "Jane Doe Ltd.",
    "password": "janes secret password (optional)"
  }'
  https://cashlink.de/business/api/v1/register/
```

> Response:

```json
{
  "public_id":"utqztAwTcQKB",
  "api_key":"0fkX3z38jPAEYAuceVRAwksNqCTyQxx7TckHRmPD"
              ^ This is the new account's USERKEY
}
```

At the most basic level you can sign up businesses with only their `business_name` (e.g., *Jane Doe Ltd.*) and an `email` address that will be their username (e.g. *jane@doe.com*). This will give you an API key for the new user, which you will use for performing further actions on the business' behalf. You should store the API key in your app or database.

For legal reasons, you must only register email addresses that you have verified valid and whose owners explicitly confirmed they are okay with receiving email from CASHLINK.

If you also provide the optional `password` field, these users will also be able to log in on the CASHLINK website to see their payments etc. If you don't provide a password, users need to go through the "Forgot Password" procedure on the CASHLINK website to log in.

The business `public_id` is a unique identifier for the newly created business. If you're using webhooks, this identifier will be contained in every event concerning that business (see "Events and Webhooks" for details).

The `api_key` value will be used for authenticating the business in other API actions, like creating a payment link.


### Terms and conditions

Every business must accept the terms and conditions of CASHLINK prior to signing up for the service.  You may include text along the following lines in your onboarding form:

By clicking on the button you agree to the [Terms and Conditions for Businesses](https://cashlink.io/terms-business) and [privacy policy](https://cashlink.io/privacy) of CASHLINK.

Mit Klick auf den Button stimmen Sie den [AGB für Unternehmen](https://cashlink.de/business/terms-business) und [Datenschutzbestimmungen](https://cashlink.de/business/privacy) der CASHLINK Payments GmbH zu.

### Providing legal information

```sh
curl
  -X POST
  -H 'Content-Type: application/json'
  -H 'X-Api-Key: APPKEY'
  --data '{
    "business_type": "company",
    "owner_first_name": "Jane",
    "owner_last_name": "Doe",
    "owner_street": "Jane Street",
    "owner_street_number": "1A",
    "owner_zipcode": "12345",
    "owner_city": "Jane City",
    "owner_birthday": "1990-01-01",
    "payout_account": "DE12500105170648489890",
    "email": "info@janedoe.com",
    "password": "janes secret password (optional)",
    "business_name": "Jane Doe Ltd.",
    "business_tax_id": "999999",
    "business_street": "Company street (companies only)",
    "business_street_number": "5 (companies only)",
    "business_zipcode": "23456 (companies only)",
    "business_city": "Company city (companies only)",
    "business_phone_number": "069 12345 (other formats OK)",
    "webhook_url": "https://yourdomain.com/webhook/payments"
  }'
  https://cashlink.de/business/api/v1/register
```

To complete the sign-up, every business has to provide a bunch of business-related information. This includes the business address, tax ID, information about a business owner or president, the payout account, etc.

Information not provided when creating the account will have to be provided by your users on the CASHLINK website after receiving their first payment. (Note that this also means users will have to be able to log in to the CASHLINK website, so you'll have to send them their CASHLINK password or give them instructions on how to reset their password.)

If your application already has your user's business' information, we recommend you pass that information to the CASHLINK API in the registration API call.

**Note:**

- `business_type` must be one of `individual` or `company`. For companies, both the owner's/representative's address and the company address will have to be provided by the platform or the company in order to receive payments.
- `email` address must be unique.
- `business_phone_number` may be in various formats, including "069 / 12345", "069 12345", "+49 69 12345", etc.
- If `password` is omitted, the account must reset their password before logging in to the CASHLINK site.
- If the optional `webhook_url` is given, an HTTP request will be sent to this URL for any events related to that business happen (e.g., new successful payments). If you set the `webhook_url` field, the response will include a "secret" value. This value is used to verify the webhook signature. See "Events and Webhooks" for details. 
