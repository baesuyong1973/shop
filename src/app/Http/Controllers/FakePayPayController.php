<?php

namespace App\Http\Controllers;

use App\Payments\FakeDepositGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The payment page of FakeDepositGateway: lets a developer (or an E2E test)
 * pay or abandon a deposit without a real PayPay account. Only reachable
 * when PAYMENT_DRIVER=fake.
 */
class FakePayPayController extends Controller
{
    public function show(string $reference): View
    {
        abort_unless(config('payment.driver') === 'fake', 404);
        $payment = FakeDepositGateway::get($reference) ?? abort(404);

        return view('fake-paypay', ['reference' => $reference, 'payment' => $payment]);
    }

    public function update(Request $request, string $reference): RedirectResponse
    {
        abort_unless(config('payment.driver') === 'fake', 404);
        $payment = FakeDepositGateway::get($reference) ?? abort(404);
        abort_unless($payment['status'] === FakeDepositGateway::STATUS_CREATED, 409);

        $status = $request->boolean('pay') ? FakeDepositGateway::STATUS_COMPLETED : FakeDepositGateway::STATUS_CANCELED;
        FakeDepositGateway::put($reference, ['status' => $status] + $payment);

        return redirect()->away($payment['return_url']);
    }
}
