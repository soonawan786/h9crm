<?php

namespace App\Http\Controllers\SuperAdmin;

use Carbon\Carbon;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use Razorpay\Api\Api;
use App\Models\Module;
use GuzzleHttp\Client;
use App\Models\Company;
use App\Models\Country;
use PayPal\Api\Agreement;
use Illuminate\Support\Str;
use PayPal\Rest\ApiContext;
use App\Scopes\CompanyScope;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\Package;
use Illuminate\Support\Facades\DB;
use Mollie\Laravel\Facades\Mollie;
use App\Models\OfflinePaymentMethod;
use Illuminate\Support\Facades\View;
use PayPal\Auth\OAuthTokenCredential;
use Unicodeveloper\Paystack\Paystack;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Config;
use App\Models\SuperAdmin\Subscription;
use Illuminate\Support\Facades\Session;
use App\Models\SuperAdmin\MollieInvoice;
use App\Models\SuperAdmin\PaypalInvoice;
use Illuminate\Support\Facades\Redirect;
use PayPal\Api\AgreementStateDescriptor;
use App\Models\SuperAdmin\OfflineInvoice;
use App\Models\SuperAdmin\PayfastInvoice;
use App\Traits\SuperAdmin\MollieSettings;
use App\Traits\SuperAdmin\StripeSettings;
use App\Models\SuperAdmin\PaystackInvoice;
use App\Models\SuperAdmin\RazorpayInvoice;
use App\Traits\SuperAdmin\PaystackSettings;
use App\Models\SuperAdmin\OfflinePlanChange;
use Illuminate\Support\Facades\Notification;
use App\Models\SuperAdmin\GlobalSubscription;
use App\Models\SuperAdmin\MollieSubscription;
use App\Models\SuperAdmin\PayfastSubscription;
use App\DataTables\SuperAdmin\InvoiceDataTable;
use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\AuthorizationInvoice;
use App\Models\SuperAdmin\PaystackSubscription;
use App\Models\SuperAdmin\RazorpaySubscription;
use App\Models\SuperAdmin\AuthorizeSubscription;
use Illuminate\Contracts\Foundation\Application;
use Stripe\PaymentIntent as StripePaymentIntent;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Notifications\SuperAdmin\CompanyUpdatedPlan;
use App\DataTables\SuperAdmin\OfflinePlanChangeDataTable;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Http\Requests\SuperAdmin\StripePayment\PaymentRequest;
use App\Http\Requests\SuperAdmin\Billing\OfflinePaymentRequest;
use App\Http\Requests\SuperAdmin\StripePayment\StripeValidateRequest;

class BillingController extends AccountBaseController
{
    use StripeSettings, PaystackSettings, MollieSettings;

    public function __construct()
    {
        parent::__construct();

        $this->paymentGatewatActive = false;
        $this->stripeSettings = GlobalPaymentGatewayCredentials::first();

        if($this->stripeSettings->paypal_status == 'active' ||
            $this->stripeSettings->stripe_status == 'active' ||
            $this->stripeSettings->razorpay_status == 'active' ||
            $this->stripeSettings->paystack_status == 'active' ||
            $this->stripeSettings->mollie_status == 'active' ||
            $this->stripeSettings->payfast_status == 'active' ||
            $this->stripeSettings->authorize_status == 'active' ||
            $this->stripeSettings->authorize_status == 'active')
        {
            $this->paymentGatewatActive = true;
        }

        $this->offlinePaymentGateways = OfflinePaymentMethod::withoutGlobalScope(CompanyScope::class)->where('status', 'yes')->whereNull('company_id')->count();
        $this->paymentActive = false;

        if(! ($this->paymentGatewatActive == false && $this->offlinePaymentGateways == 0)){
            $this->paymentActive = true;
        }

        $this->setStripConfigs();

        $this->pageTitle = 'superadmin.menu.billing';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()));
            return $next($request);
        });
    }

    public function index()
    {
        $this->activeSettingMenu = 'billing';
        $this->company = Company::with('currency', 'package')
            ->withCount(['employees', 'fileStorage'])
            ->withSum('fileStorage', 'size')
            ->with(['companyAddress' => function ($query) {
                return $query->where('is_default', 1);
            }])
            ->findOrFail(company()->id);

        $tab = request('tab');

        switch ($tab) {
        case 'purchase-history':
            return $this->billing();
        case 'offline-request':
            return $this->offlineRequest();
        default:
            $this->view = 'super-admin.billing.ajax.plan';
                break;
        }

        $this->activeTab = $tab ?: 'plan';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('super-admin.billing.index', $this->data);

    }

    public function billing()
    {
        $dataTable = new InvoiceDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'company';

        $this->view = 'super-admin.billing.ajax.billing';

        return $dataTable->render('super-admin.billing.index', $this->data);
    }

    public function offlineRequest()
    {
        $dataTable = new OfflinePlanChangeDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'company';

        $this->view = 'super-admin.billing.ajax.billing';

        return $dataTable->render('super-admin.billing.index', $this->data);
    }

    /**
     * @param Request $request
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function upgradePlan(Request $request)
    {
        $this->pageTitle = 'superadmin.menu.packages';
        $this->packages = Package::with('currency')->whereNot('default', 'trial')->where('is_private', 0)->get();

        $this->modulesData = Module::where('module_name', '<>', 'settings')
            ->where('module_name', '<>', 'dashboards')
            ->whereNotIn('module_name', Module::disabledModuleArray())
            ->get();

        $this->offlineMethods = OfflinePaymentMethod::withoutGlobalScope(CompanyScope::class)->whereNull('company_id')->where('status', 'yes')->count();
        $this->annualPlan = $this->packages->filter(function ($value, $key) {
            return $value->annual_status == 1;
        })->count();

        $this->monthlyPlan = $this->packages->filter(function ($value, $key) {
            return $value->monthly_status == 1;
        })->count();

        return view('super-admin.billing.upgrade_plan', $this->data);
    }

    /**
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function packages()
    {
        $this->packages = Package::where('default', 'no')->where('is_private', 0)->get();
        $this->modulesData = Module::where('module_name', '<>', 'settings')
            ->where('module_name', '<>', 'dashboards')
            ->whereNotIn('module_name', Module::disabledModuleArray())
            ->get();
        $this->stripeSettings = GlobalPaymentGatewayCredentials::first();
        $this->offlineMethods = OfflinePaymentMethod::withoutGlobalScope(CompanyScope::class)->whereNull('company_id')->where('status', 'yes')->count();
        $this->pageTitle = 'app.menu.packages';
        $this->company = company();

        $this->annualPlan = $this->packages->filter(function ($value, $key) {
            return $value->annual_status == 1;
        })->count();

        $this->monthlyPlan = $this->packages->filter(function ($value, $key) {
            return $value->monthly_status == 1;
        })->count();

        return view('super-admin.billing.package', $this->data);
    }

    public function selectPackage(Request $request, $packageID)
    {
        $this->package = Package::findOrFail($packageID);
        $this->free = false;

        if((!round($this->package->monthly_price) > 0 && $this->package->default == 'no' ) || $this->package->is_free == 1  ){
            $this->free = true;
        }

        $this->company = company();
        $this->type    = $request->type;
        $this->stripeSettings = GlobalPaymentGatewayCredentials::first();
        $this->logo = $this->company->logo_url;

        $this->countries = Country::all();
        $this->methods = OfflinePaymentMethod::withoutGlobalScope(CompanyScope::class)->where('status', 'yes')->whereNull('company_id')->get();

        $this->payFastHtml = (new PayFastController)->payFastPayment($this->package, $this->type, $this->company);

        return View::make('super-admin.billing.payment-method-show', $this->data);
    }

    public function stripeValidate(StripeValidateRequest $request)
    {

        $this->customerDetail = [
            'email' => $request->stripeEmail,
            'name' => $request->clientName,
            'line1' => $request->line1,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
        ];
        $this->type = $request->type;

        $this->intent = '';
        $this->stripeSettings = GlobalPaymentGatewayCredentials::first();

        $this->package = Package::findOrFail($request->plan_id);

        $this->intent = $this->company->createSetupIntent([
            'description' => $this->package->name. $request->type . ' Payment',
            'metadata' => ['integration_check' => 'accept_a_payment']
        ]);

        $view = view('super-admin.billing.ajax.stripe-payment', $this->data)->render();
        $buttonView = view('super-admin.billing.ajax.stripe-button', $this->data)->render();

        return Reply::dataOnly(['view' => $view, 'buttonView' => $buttonView]);
    }

    public function payment(PaymentRequest $request)
    {
        $token = $request->payment_method;
        $email = $request->stripeEmail;
        $plan = Package::find($request->plan_id);

        $stripe = DB::table('stripe_invoices')
            ->join('packages', 'packages.id', 'stripe_invoices.package_id')
            ->selectRaw('stripe_invoices.id , "Stripe" as method, stripe_invoices.pay_date as paid_on ,stripe_invoices.next_pay_date')
            ->whereNotNull('stripe_invoices.pay_date')
            ->where('stripe_invoices.company_id', company()->id);

        $razorpay = DB::table('razorpay_invoices')
            ->join('packages', 'packages.id', 'razorpay_invoices.package_id')
            ->selectRaw('razorpay_invoices.id ,"Razorpay" as method, razorpay_invoices.pay_date as paid_on ,razorpay_invoices.next_pay_date')
            ->whereNotNull('razorpay_invoices.pay_date')
            ->where('razorpay_invoices.company_id', company()->id);

        $allInvoices = DB::table('paypal_invoices')
            ->join('packages', 'packages.id', 'paypal_invoices.package_id')
            ->selectRaw('paypal_invoices.id, "Paypal" as method, paypal_invoices.paid_on,paypal_invoices.next_pay_date')
            ->where('paypal_invoices.status', 'paid')
            ->whereNull('paypal_invoices.end_on')
            ->where('paypal_invoices.company_id', company()->id)
            ->union($stripe)
            ->union($razorpay)
            ->get();

        $firstInvoice = $allInvoices->sortByDesc(function ($temp, $key) {
            return Carbon::parse($temp->paid_on)->getTimestamp();
        })->first();

        $subcriptionCancel = true;

        if (!is_null($firstInvoice) && $firstInvoice->method == 'Paypal') {
            $subcriptionCancel = $this->cancelSubscriptionPaypal();
        }

        if (!is_null($firstInvoice) && $firstInvoice->method == 'Razorpay') {
            $subcriptionCancel = $this->cancelSubscriptionPaypal();
        }

        if ($subcriptionCancel) {

            if ($plan->max_employees < $this->company->employees->count()) {
                \session()->put('error', 'You can\'t downgrade package because your employees length is ' . $this->company->employees->count() . ' and package max employees lenght is ' . $plan->max_employees);
                return redirect()->route('billing.upgrade_plan');
            }

            $company = Company::withoutGlobalScope(CompanyScope::class)->findOrFail($this->company->id);

            $subscription = $company->subscriptions;

            try {
                if ($subscription->count() > 0) {
                    $company->subscription('primary')
                        ->swap($plan->{'stripe_' . $request->type . '_plan_id'});
                }
                else {
                    $company->newSubscription(
                        'primary',
                        $plan->{'stripe_' . $request->type . '_plan_id'}
                    )->create($token, [
                        'email' => $email,
                    ]);
                }

                $subscription = new GlobalSubscription();
                $subscription->company_id = company()->id;
                $subscription->package_id = $plan->id;
                $subscription->package_type = $request->type;
                $subscription->gateway_name = 'stripe';
                $subscription->subscription_status = 'active';
                $subscription->subscribed_on_date = Carbon::now()->format('Y-m-d H:i:s');
                $subscription->save();

                $company->package_id = $plan->id;
                $company->package_type = $request->type;

                // Set company status active
                $company->status = 'active';
                $company->licence_expire_on = null;

                $company->save();

                // Send superadmin notification
                $generatedBy = User::withoutGlobalScopes([CompanyScope::class, 'active'])
                    ->whereNull('company_id')
                    ->where('status', 'active')
                    ->get();

                $allAdmins = User::allAdmins($company->id);
                Notification::send($generatedBy, new CompanyUpdatedPlan($company, $plan->id));
                Notification::send($allAdmins, new CompanyUpdatedPlan($company, $plan->id));

                Session::put('success', __('superadmin.paymentSuccessfullyDone', ['package' => company()->package->name, 'planType' => company()->package_type]));
                return Redirect::route('billing.index');
            } catch (IncompletePayment $exception) {
                return view('cashier::payment', [
                    'stripeKey' => config('cashier.key'),
                    'payment' => new Payment(
                        StripePaymentIntent::retrieve($exception->payment->id, Cashier::stripeOptions())
                    ),
                    'redirect' => route('billing.index'),
                ]);
            } catch (\Exception $exception) {
                \session()->put('error', $exception->getMessage());
                return redirect()->route('billing.upgrade_plan');
            }

        }
    }

    public function download(Request $request, $invoiceId)
    {
        $globalData = GlobalSetting::first();
        return $this->company->downloadInvoice($invoiceId, [
            'vendor'  => $this->company->company_name,
            'product' => $this->company->package->name,
            'global' => $globalData,
            'logo' => $globalData->logo_url,
        ]);
    }

    public function cancelSubscriptionPaypal()
    {
        $credential = GlobalPaymentGatewayCredentials::first();
        $paypal_conf = Config::get('paypal');
        $api_context = new ApiContext(new OAuthTokenCredential($credential->paypal_client_id, $credential->paypal_secret));
        $api_context->setConfig($paypal_conf['settings']);

        $paypalInvoice = PaypalInvoice::whereNotNull('transaction_id')->whereNull('end_on')
            ->where('company_id', company()->id)->where('status', 'paid')->first();

        if ($paypalInvoice) {
            $agreementId = $paypalInvoice->transaction_id;
            $agreement = new Agreement();

            $agreement->setId($agreementId);
            $agreementStateDescriptor = new AgreementStateDescriptor();
            $agreementStateDescriptor->setNote('Cancel the agreement');

            try {
                $agreement->cancel($agreementStateDescriptor, $api_context);
                $cancelAgreementDetails = Agreement::get($agreement->getId(), $api_context);

                // Set subscription end date
                $paypalInvoice->end_on = Carbon::parse($cancelAgreementDetails->agreement_details->final_payment_date)->format('Y-m-d H:i:s');
                $paypalInvoice->save();
            } catch (\Exception $ex) {
                Session::put('error', $ex->getMessage());
                return false;
            }

            return true;
        }

    }

    public function razorpayPayment(Request $request)
    {
        $credential = GlobalPaymentGatewayCredentials::first();

        if($credential->razorpay_mode == 'test') {
            $apiKey    = $credential->test_razorpay_key;
            $secretKey = $credential->test_razorpay_secret;
        }
        else {
            $apiKey = $credential->live_razorpay_key;
            $secretKey = $credential->live_razorpay_secret;
        }

        $paymentId = request('paymentId');
        $razorpaySignature = $request->razorpay_signature;
        $subscriptionId = $request->subscription_id;
        try{
            $api = new Api($apiKey, $secretKey);

            $plan = Package::with('currency')->find($request->plan_id);
            $type = $request->type;

            $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $secretKey);
        }
        catch (\Exception $e){
            \session()->put('error', $e->getMessage());
            return Reply::redirect(route('billing.upgrade_plan'));
        }

        if ($expectedSignature === $razorpaySignature) {

            if ($plan->max_employees < $this->company->employees->count()) {
                \session()->put('error', 'You can\'t downgrade package because your employees length is ' . $this->company->employees->count() . ' and package max employees lenght is ' . $plan->max_employees);
                return Reply::redirect(route('billing.upgrade_plan'));
            }

            try {
                $api->payment->fetch($paymentId);

                $payment = $api->payment->fetch($paymentId); // Returns a particular payment

                if ($payment->status == 'authorized') {
                    $payment->capture(array('amount' => $payment->amount, 'currency' => $plan->currency->currency_code));
                }

                $company = $this->company;
                $company->package_id = $plan->id;
                $company->package_type = $type;

                // Set company status active
                $company->status = 'active';
                $company->licence_expire_on = null;
                $company->save();

                $subscription = new GlobalSubscription();
                $subscription->transaction_id      = $subscriptionId;
                $subscription->company_id           = company()->id;
                $subscription->currency_id          = $plan->currency_id;
                $subscription->razorpay_id          = $paymentId;
                $subscription->razorpay_plan        = $type;
                $subscription->quantity             = 1;
                $subscription->package_id           = $plan->id;
                $subscription->package_type         = $type;
                $subscription->gateway_name         = 'razorpay';
                $subscription->subscription_status  = 'active';
                $subscription->subscribed_on_date   = Carbon::now()->format('Y-m-d H:i:s');
                $subscription->save();

                // Send superadmin notification
                $generatedBy = User::withoutGlobalScopes([CompanyScope::class, 'active'])->whereNull('company_id')->get();
                $allAdmins = User::allAdmins($company->id);
                Notification::send($generatedBy, new CompanyUpdatedPlan($company, $plan->id));
                Notification::send($allAdmins, new CompanyUpdatedPlan($company, $plan->id));
                Session::put('success', __('superadmin.paymentSuccessfullyDone', ['package' => company()->package->name, 'planType' => company()->package_type]));
                return Reply::redirect(route('billing.index'));
            } catch (\Exception $e) {
                \session()->put('error', $e->getMessage());
                return Reply::redirect(route('billing.upgrade_plan'));
            }

        }

    }

    public function razorpaySubscription(Request $request)
    {
        $credential = GlobalPaymentGatewayCredentials::first();

        $plan = Package::find($request->plan_id);
        $type = $request->type;

        $planID = ($type == 'annual') ? $plan->razorpay_annual_plan_id : $plan->razorpay_monthly_plan_id;

        if($credential->razorpay_mode == 'test') {
            $apiKey    = $credential->test_razorpay_key;
            $secretKey = $credential->test_razorpay_secret;
        }
        else {
            $apiKey = $credential->live_razorpay_key;
            $secretKey = $credential->live_razorpay_secret;
        }

        $api        = new Api($apiKey, $secretKey);
        $subscription  = $api->subscription->create(array('plan_id' => $planID, 'customer_notify' => 1, 'total_count' => 100));

        return Reply::dataOnly(['subscriprion' => $subscription->id]);
    }

    public function cancelSubscriptionRazorpay()
    {
        $credential = GlobalPaymentGatewayCredentials::first();

        if($credential->razorpay_mode == 'test') {
            $apiKey    = $credential->test_razorpay_key;
            $secretKey = $credential->test_razorpay_secret;
        }
        else {
            $apiKey = $credential->live_razorpay_key;
            $secretKey = $credential->live_razorpay_secret;
        }

        $api       = new Api($apiKey, $secretKey);

        // Get subscription for unsubscribe
        $subscriptionData = RazorpaySubscription::where('company_id', company()->id)->whereNull('ends_at')->first();

        if ($subscriptionData) {
            try {
                $subscription  = $api->subscription->fetch($subscriptionData->subscription_id);

                if ($subscription->status == 'active') {

                    // Unsubscribe plan
                    $subData = $api->subscription->fetch($subscriptionData->subscription_id)->cancel(['cancel_at_cycle_end' => 0]);

                    // Plan will be end on this date
                    $subscriptionData->ends_at = \Carbon\Carbon::createFromTimestamp($subData->end_at)->format('Y-m-d');
                    $subscriptionData->save();
                }

            } catch (\Exception $ex) {
                return false;
            }
            return true;
        }
    }

    public function cancelSubscription(Request $request)
    {
        $type = $request->type;
        $credential = GlobalPaymentGatewayCredentials::first();

        if ($type == 'paypal') {
            $paypal_conf = Config::get('paypal');
            $api_context = new ApiContext(new OAuthTokenCredential($credential->paypal_client_id, $credential->paypal_secret));
            $api_context->setConfig($paypal_conf['settings']);

            $paypalInvoice = PaypalInvoice::whereNotNull('transaction_id')->whereNull('end_on')
                ->where('company_id', company()->id)->where('status', 'paid')->first();

            if ($paypalInvoice) {
                $agreementId = $paypalInvoice->transaction_id;
                $agreement = new Agreement();
                $paypalInvoice = PaypalInvoice::whereNotNull('transaction_id')->whereNull('end_on')
                    ->where('company_id', company()->id)->where('status', 'paid')->first();

                $agreement->setId($agreementId);
                $agreementStateDescriptor = new AgreementStateDescriptor();
                $agreementStateDescriptor->setNote('Cancel the agreement');

                try {
                    $agreement->cancel($agreementStateDescriptor, $api_context);
                    $cancelAgreementDetails = Agreement::get($agreement->getId(), $api_context);

                    // Set subscription end date
                    $paypalInvoice->end_on = Carbon::parse($cancelAgreementDetails->agreement_details->final_payment_date)->format('Y-m-d H:i:s');
                    $paypalInvoice->save();
                } catch (\Exception $ex) {
                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }
            }
        } elseif ($type == 'razorpay') {

            $apiKey    = $credential->razorpay_key;
            $secretKey = $credential->razorpay_secret;
            $api       = new Api($apiKey, $secretKey);

            // Get subscription for unsubscribe
            $subscriptionData = RazorpaySubscription::where('company_id', company()->id)->whereNull('ends_at')->first();

            if ($subscriptionData) {
                try {
                    $subscription  = $api->subscription->fetch($subscriptionData->subscription_id);

                    if ($subscription->status == 'active') {

                        // Unsubscribe plan
                        $subData = $api->subscription->fetch($subscriptionData->subscription_id)->cancel(['cancel_at_cycle_end' => 1]);

                        // Plan will be end on this date
                        $subscriptionData->ends_at = \Carbon\Carbon::createFromTimestamp($subData->end_at)->format('Y-m-d');
                        $subscriptionData->save();
                    }

                } catch (\Exception $ex) {
                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }
                return Reply::redirectWithError(route('billing.packages'), 'There is no data found for this subscription');
            }

        } elseif ($type == 'paystack') {
            // Get subscription for unsubscribe
            $this->setPaystackConfigs();
            $subscriptionData = PaystackSubscription::where('company_id', company()->id)->where('status', 'active')->first();

            if ($subscriptionData) {
                try {
                    $paystack = new Paystack();
                    $request->code = $subscriptionData->subscription_id;
                    $request->token = $subscriptionData->token;

                    $paystack->disableSubscription();

                    $subscriptionData->status = 'inactive';
                    $subscriptionData->save();
                } catch (\Exception $ex) {
                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }
            }

        } elseif ($type == 'mollie') {
            // Get subscription for unsubscribe
            $this->setMollieConfigs();
            $subscriptionData = MollieSubscription::where('company_id', company()->id)->first();

            if ($subscriptionData) {
                try {
                    $customer = Mollie::api()->customers()->get($subscriptionData->customer_id);

                    Mollie::api()->subscriptions()->cancelFor($customer, $subscriptionData->subscription_id);

                    $subscriptionData->ends_at = Carbon::now();
                    $subscriptionData->save();
                } catch (\Exception $ex) {

                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }

            }

        }  elseif ($type == 'authorize') {
            // Get subscription for unsubscribe
            $this->setMollieConfigs();
            $subscriptionData = AuthorizeSubscription::where('company_id', company()->id)->first();

            if ($subscriptionData) {
                try {

                    $credential = GlobalPaymentGatewayCredentials::first();
                    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();

                    $merchantAuthentication->setName($credential->authorize_api_login_id);
                    $merchantAuthentication->setTransactionKey($credential->authorize_transaction_key);

                    // Set the transaction's refId
                    $refId = 'ref' . time();

                    $request = new AnetAPI\ARBCancelSubscriptionRequest();
                    $request->setMerchantAuthentication($merchantAuthentication);
                    $request->setRefId($refId);
                    $request->setSubscriptionId($subscriptionData->subscription_id);

                    $controller = new AnetController\ARBCancelSubscriptionController($request);

                    if($credential->authorize_environment == 'sandbox') {
                        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                    }
                    else {
                        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                    }

                    if (($response != null) && ($response->getMessages()->getResultCode() == 'Ok'))
                    {

                        $subscriptionData->ends_at = Carbon::now();
                        $subscriptionData->save();

                    }
                    else
                    {
                        $errorMessages = $response->getMessages()->getMessage();
                        return Reply::error($errorMessages[0]->getText());

                    }


                } catch (\Exception $ex) {

                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }
            }

        } elseif ($type == 'payfast') {
            $credential = GlobalPaymentGatewayCredentials::first();
            $payfastInvoice = PayfastInvoice::orderBy('id', 'DESC')->first();
            $date = Carbon::now();
            try{
                $client = new Client();
                $res = $client->request('PUT', 'https://sandbox.payfast.co.za/subscriptions/'.$payfastInvoice->token.'/cancel',
                    ['merchant-id' => $credential->payfast_key, 'version' => 'v1' , 'timestamp' => $date->toDateTimeString(), 'signature' => $payfastInvoice->signature]);

                $conversionRate = $res->getBody();
                $conversionRate = json_decode($conversionRate, true);

                if($conversionRate['status'] == 'success'){
                    $paydate = $payfastInvoice->pay_date;

                    if($this->company->package_type == 'monthly'){
                        $newDate = Carbon::createFromDate($paydate)->addMonth()->format('Y-m-d');
                    }
                    else {
                        $newDate = Carbon::createFromDate($paydate)->addYear()->format('Y-m-d');
                    }

                    $subscription = PayfastSubscription::orderBy('id', 'DESC')->first();
                    $subscription->ends_at = $newDate;
                    $subscription->save();

                }

            } catch(\Exception $ex) {

                Session::put('error', $ex->getMessage());
                return redirect()->route('billing.upgrade_plan');
            }

        } else {
            $company = company();
            $subscription = Subscription::where('company_id', company()->id)->whereNull('ends_at')->first();

            if ($subscription) {
                try {
                    $company->subscription('primary')->cancel();
                } catch (\Exception $ex) {
                    Session::put('error', $ex->getMessage());
                    return redirect()->route('billing.upgrade_plan');
                }
            }

        }

        return Reply::redirect(route('billing.index'), __('messages.unsubscribeSuccess'));
    }

    public function offlinePayment(Request $request)
    {
        $this->package_id = $request->package_id;
        $this->offlineId = $request->offlineId;
        $this->type = $request->type;
        return view('super-admin.billing.offline-payment', $this->data);
    }

    public function offlinePaymentSubmit(OfflinePaymentRequest $request)
    {
        $checkAlreadyRequest = OfflinePlanChange::where('company_id', company()->id)
            ->where('status', 'pending')
            ->first();

        if ($checkAlreadyRequest) {
            return Reply::error(__('superadmin.messages.alreadyRaisedRequest'));
        }

        $package = Package::find($request->package_id);

        // create offline plan change request
        $offlinePlanChange = new OfflinePlanChange();
        $offlinePlanChange->package_id = $request->package_id;
        $offlinePlanChange->package_type = $request->type;
        $offlinePlanChange->company_id = company()->id;
        $offlinePlanChange->offline_method_id = $request->offline_id;
        $offlinePlanChange->description = $request->description;
        $offlinePlanChange->amount = $request->type == 'monthly' ? $package->monthly_price : $package->annual_price;
        $offlinePlanChange->pay_date = Carbon::now()->format('Y-m-d');
        $offlinePlanChange->next_pay_date = $request->type == 'monthly' ? Carbon::now()->addMonth()->format('Y-m-d') : Carbon::now()->addYear()->format('Y-m-d');

        if ($request->hasFile('slip')) {
            $offlinePlanChange->file_name = Files::upload($request->slip, OfflinePlanChange::FILE_PATH, false, false, false);
        }

        $offlinePlanChange->save();

        Session::put('success', __('superadmin.offlinePlanChangeRequestReceived'));

        return Reply::redirect(route('billing.index', ['tab' => 'offline-request']));
    }

    public function freePlan(Request $request)
    {
            // create offline invoice
            $offlineInvoice = new OfflineInvoice();
            $offlineInvoice->package_id = $request->package_id;
            $offlineInvoice->package_type = $request->type;
            $offlineInvoice->amount = 0;
            $offlineInvoice->pay_date = Carbon::now()->format('Y-m-d');
            $offlineInvoice->status = 'paid';
            $offlineInvoice->next_pay_date = $request->type == 'monthly' ? Carbon::now()->addMonth()->format('Y-m-d') : Carbon::now()->addYear()->format('Y-m-d');
            $offlineInvoice->save();

            $company = company();
            // Change company package
            $company->package_id = $request->package_id;
            $company->package_type = $request->type;
            $company->save();

            Session::put('success', __('superadmin.paymentSuccessfullyDone', ['package' => company()->package->name, 'planType' => company()->package_type]));

        return Reply::redirect(route('billing.index'));
    }

}
