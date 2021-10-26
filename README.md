# Seal Subscriptions Merchant API client in PHP

This repository contains the Seal Subscriptions Merchant API client, which allows you to make API calls to the Seal Subscriptions app. 
https://apps.shopify.com/seal-subscriptions

The full Seal Subscriptions Merchant API documentation is accessible on the following URL:
https://www.sealsubscriptions.com/articles/merchant-api-documentation

This repository contains a demo-api-call.php file which allows you to easily start using the API. The file contains a few sample API calls. 
Before using it, **make sure to replace the SEAL_API_TOKEN and SEAL_API_SECRET with your shop's token and secret.**

The demo-webhook-endpoint.php contains a sample webhook endpoint, showcasing how you can read the webhook data send from the Seal Subscriptions server to your endpoint.
You can use the webhooks to get notified when a new subscription is created in the shop or when a subscription is edited.
