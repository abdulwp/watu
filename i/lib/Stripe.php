<?php

<<<<<<< HEAD
// Tested on PHP 5.2, 5.3

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('Stripe needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Stripe needs the JSON PHP extension.');
}

// Stripe singleton
require(dirname(__FILE__) . '/Stripe/Stripe.php');

// Utilities
require(dirname(__FILE__) . '/Stripe/Util.php');
require(dirname(__FILE__) . '/Stripe/Util/Set.php');

// Errors
require(dirname(__FILE__) . '/Stripe/Error.php');
require(dirname(__FILE__) . '/Stripe/ApiError.php');
require(dirname(__FILE__) . '/Stripe/ApiConnectionError.php');
require(dirname(__FILE__) . '/Stripe/AuthenticationError.php');
require(dirname(__FILE__) . '/Stripe/CardError.php');
require(dirname(__FILE__) . '/Stripe/InvalidRequestError.php');

// Plumbing
require(dirname(__FILE__) . '/Stripe/Object.php');
require(dirname(__FILE__) . '/Stripe/ApiRequestor.php');
require(dirname(__FILE__) . '/Stripe/ApiResource.php');
require(dirname(__FILE__) . '/Stripe/SingletonApiResource.php');
require(dirname(__FILE__) . '/Stripe/List.php');

// Stripe API Resources
require(dirname(__FILE__) . '/Stripe/Account.php');
require(dirname(__FILE__) . '/Stripe/Charge.php');
require(dirname(__FILE__) . '/Stripe/Customer.php');
require(dirname(__FILE__) . '/Stripe/Invoice.php');
require(dirname(__FILE__) . '/Stripe/InvoiceItem.php');
require(dirname(__FILE__) . '/Stripe/Plan.php');
require(dirname(__FILE__) . '/Stripe/Token.php');
require(dirname(__FILE__) . '/Stripe/Coupon.php');
require(dirname(__FILE__) . '/Stripe/Event.php');
require(dirname(__FILE__) . '/Stripe/Transfer.php');
require(dirname(__FILE__) . '/Stripe/Recipient.php');
=======
// Stripe singleton
require(dirname(__FILE__) . '/lib/Stripe.php');

// Utilities
require(dirname(__FILE__) . '/lib/Util/AutoPagingIterator.php');
require(dirname(__FILE__) . '/lib/Util/RequestOptions.php');
require(dirname(__FILE__) . '/lib/Util/Set.php');
require(dirname(__FILE__) . '/lib/Util/Util.php');

// HttpClient
require(dirname(__FILE__) . '/lib/HttpClient/ClientInterface.php');
require(dirname(__FILE__) . '/lib/HttpClient/CurlClient.php');

// Errors
require(dirname(__FILE__) . '/lib/Error/Base.php');
require(dirname(__FILE__) . '/lib/Error/Api.php');
require(dirname(__FILE__) . '/lib/Error/ApiConnection.php');
require(dirname(__FILE__) . '/lib/Error/Authentication.php');
require(dirname(__FILE__) . '/lib/Error/Card.php');
require(dirname(__FILE__) . '/lib/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/lib/Error/Permission.php');
require(dirname(__FILE__) . '/lib/Error/RateLimit.php');

// Plumbing
require(dirname(__FILE__) . '/lib/ApiResponse.php');
require(dirname(__FILE__) . '/lib/JsonSerializable.php');
require(dirname(__FILE__) . '/lib/StripeObject.php');
require(dirname(__FILE__) . '/lib/ApiRequestor.php');
require(dirname(__FILE__) . '/lib/ApiResource.php');
require(dirname(__FILE__) . '/lib/SingletonApiResource.php');
require(dirname(__FILE__) . '/lib/AttachedObject.php');
require(dirname(__FILE__) . '/lib/ExternalAccount.php');

// Stripe API Resources
require(dirname(__FILE__) . '/lib/Account.php');
require(dirname(__FILE__) . '/lib/AlipayAccount.php');
require(dirname(__FILE__) . '/lib/ApplePayDomain.php');
require(dirname(__FILE__) . '/lib/ApplicationFee.php');
require(dirname(__FILE__) . '/lib/ApplicationFeeRefund.php');
require(dirname(__FILE__) . '/lib/Balance.php');
require(dirname(__FILE__) . '/lib/BalanceTransaction.php');
require(dirname(__FILE__) . '/lib/BankAccount.php');
require(dirname(__FILE__) . '/lib/BitcoinReceiver.php');
require(dirname(__FILE__) . '/lib/BitcoinTransaction.php');
require(dirname(__FILE__) . '/lib/Card.php');
require(dirname(__FILE__) . '/lib/Charge.php');
require(dirname(__FILE__) . '/lib/Collection.php');
require(dirname(__FILE__) . '/lib/CountrySpec.php');
require(dirname(__FILE__) . '/lib/Coupon.php');
require(dirname(__FILE__) . '/lib/Customer.php');
require(dirname(__FILE__) . '/lib/Dispute.php');
require(dirname(__FILE__) . '/lib/Event.php');
require(dirname(__FILE__) . '/lib/FileUpload.php');
require(dirname(__FILE__) . '/lib/Invoice.php');
require(dirname(__FILE__) . '/lib/InvoiceItem.php');
require(dirname(__FILE__) . '/lib/Order.php');
require(dirname(__FILE__) . '/lib/OrderReturn.php');
require(dirname(__FILE__) . '/lib/Plan.php');
require(dirname(__FILE__) . '/lib/Product.php');
require(dirname(__FILE__) . '/lib/Recipient.php');
require(dirname(__FILE__) . '/lib/Refund.php');
require(dirname(__FILE__) . '/lib/SKU.php');
require(dirname(__FILE__) . '/lib/Source.php');
require(dirname(__FILE__) . '/lib/Subscription.php');
require(dirname(__FILE__) . '/lib/SubscriptionItem.php');
require(dirname(__FILE__) . '/lib/ThreeDSecure.php');
require(dirname(__FILE__) . '/lib/Token.php');
require(dirname(__FILE__) . '/lib/Transfer.php');
require(dirname(__FILE__) . '/lib/TransferReversal.php');
>>>>>>> branch/6.7.2
