<?php
return [
  'secret'  => env('PAYSTACK_SECRET_KEY'),
  'public'  => env('PAYSTACK_PUBLIC_KEY'),
  'base'    => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
  'callback'=> env('PAYSTACK_CALLBACK_URL'),
  'webhook' => env('PAYSTACK_WEBHOOK_URL'),
];
