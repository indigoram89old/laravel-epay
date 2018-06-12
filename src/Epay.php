<?php

namespace Indigoram89\Laravel\Epay;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Indigoram89\Laravel\Epay\Events\PaymentCompleted;
use Indigoram89\Laravel\Epay\Contracts\Epay as EpayContract;

class Epay implements EpayContract
{
	protected $config;

	public function __construct($app)
	{
		$this->config = $app['config']['epay'];
	}

	public function createPaymentLink(array $attributes, string $name = null) : string
	{
		$attributes = $this->preparePaymentAttributes($attributes);

		$query = http_build_query($attributes);

		return str_replace(
			['{query}', '{name}'], [$query, e($name)],
			'<a href="https://api.epay.com/paymentApi/merReceive?{query}">{name}</a>'
		);
	}

	public function createPaymentForm(array $attributes, string $button = null) : string
	{
		$attributes = $this->preparePaymentAttributes($attributes);

		$form = '<form action="https://api.epay.com/paymentApi/merReceive" method="POST">';

		foreach ($attributes as $name => $value) {
			$form .= str_replace(
				['{name}', '{value}'], [e($name), e($value)],
				'<input type="text" name="{name}" value="{value}">'
			);
		}

		$form .= str_replace('{button}', $button, '<input type="submit" value="{button}">');
		
		$form .= '</form>';

		return $form;
	}

	protected function preparePaymentAttributes(array $attributes)
	{
		$attributes['PAYEE_ACCOUNT'] = $this->getConfig('account');
		
		$attributes['STATUS_URL'] = $attributes['STATUS_URL'] ?? (Route::has('epay.payments.status') ? route('epay.payments.status') : '');
		
		$attributes['V2_HASH'] = md5("{$attributes['PAYEE_ACCOUNT']}:{$attributes['PAYMENT_AMOUNT']}:{$attributes['PAYMENT_UNITS']}:{$this->getConfig('api_key')}");

		return $attributes;
	}

	public function checkPaymentCompleted(Request $request) : bool
	{
		if ($this->checkPaymentSignature($request)) {
			if ($status = (int) $request->input('STATUS')) {
				return ($status === 2);
			}
		}

		return false;
	}

	protected function checkPaymentSignature(Request $request)
	{
		$params = $request->input('PAYMENT_ID') . ':';
		$params .= $request->input('ORDER_NUM') . ':';
		$params .= $this->getConfig('account') . ':';
		$params .= $request->input('PAYMENT_AMOUNT') . ':';
		$params .= $request->input('PAYMENT_UNITS') . ':';
		$params .= $request->input('PAYER_ACCOUNT') . ':';
		$params .= $request->input('STATUS') . ':';
		$params .= $request->input('TIMESTAMPGMT') . ':';

		$params .= $this->getConfig('api_key');

		return ($request->input('V2_HASH2') === md5($params));
	}

	public function dispatchPaymentCompleted(Request $request)
	{
		PaymentCompleted::dispatch($request);
	}

	public function getPaymentResponse(Request $request) : Response
	{
		return new Response('Success', 200);
	}

	public function getConfig(string $key, string $default = null)
	{
		return array_get($this->config, $key, $default);
	}

	public static function routes()
	{
		Route::prefix('epay')->namespace('Indigoram89\Laravel\Epay\Controllers')->group(function ($routes) {
			$routes->post('payments/status', 'PaymentsController@status')->name('epay.payments.status');
		});
	}
}