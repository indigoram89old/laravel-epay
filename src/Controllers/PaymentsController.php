<?php

namespace Indigoram89\Laravel\Epay\Controllers;

use Illuminate\Http\Request;
use Indigoram89\Laravel\Epay\Contracts\Epay;

class PaymentsController
{
    protected $epay;

    public function __construct(Epay $epay)
    {
        $this->epay = $epay;
    }

    public function status(Request $request)
    {
        if ($this->epay->checkPaymentCompleted($request)) {
            $this->epay->dispatchPaymentCompleted($request);
        }

        return $this->epay->getPaymentResponse($request);
    }
}
