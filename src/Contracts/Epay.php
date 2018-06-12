<?php

namespace Indigoram89\Laravel\Epay\Contracts;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface Epay
{
	public function createPaymentLink(array $attributes, string $name = null) : string;
	
	public function createPaymentForm(array $attributes, string $button = null) : string;
	
	public function checkPaymentCompleted(Request $request) : bool;
	
	public function dispatchPaymentCompleted(Request $request);
	
	public function getPaymentResponse(Request $request) : Response;

	public function getConfig(string $key, string $default = null);
	
}