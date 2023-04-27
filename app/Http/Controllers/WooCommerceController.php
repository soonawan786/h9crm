<?php

namespace App\Http\Controllers;

use App\DataTables\WooCommerceDataTable;
use App\Models\WooCommerce;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Models\WooCommerceOrder;
use Illuminate\Support\Facades\Validator;

class WooCommerceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.woo_commerce';
        $this->activeSettingMenu = 'integrate';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('woo_commerce', $this->user->modules));
            return $next($request);
        });
    }

    public function wooCreate(){
        $this->pageTitle = __('modules.woo_commerce.integrate');
        $this->woo_commerce = WooCommerce::where('user_id',$this->user->id)->first();
        return view('woo-commerce.woo-create',$this->data);

    }
    public function wooStore(Request $request){
        $validateUser = Validator::make($request->all(),[
            'website_url' => 'required',
            'client_id' => 'required',
            'secret_key' => 'required',
          ]);
          if ($validateUser->fails()) {
            return Reply::formErrors($validateUser->errors());
          }
        $ok  = WooCommerce::updateOrCreate(
            [
                'user_id'   => $this->user->id,
            ],
            [
                'user_id' => $this->user->id,
                'website_url' => $request->website_url,
                'client_id' => $request->client_id,
                'secret_key' => $request->secret_key,
            ]);
        if($ok){
            return Reply::success('messages.recordSaved');
        }
    }

    public function wooIndex(WooCommerceDataTable $dataTable){
        $this->pageTitle = __('modules.woo_commerce.woo_orders');
        $this->woo_commerce = WooCommerce::where('user_id',$this->user->id)->first();
        return $dataTable->render('woo-commerce.index', $this->data);
    }
    public function wooOrders(){

        $wooCommerceCredential = WooCommerce::where('user_id',$this->user->id)->first();

        $url = $wooCommerceCredential->website_url.'/wp-json/wc/v3/orders';

        $client_id = $wooCommerceCredential->client_id;
        $secret_key = $wooCommerceCredential->secret_key;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        // curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, "$client_id:$secret_key");
        $resp = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        //dd(json_decode($resp));

        if($status_code=='200'){
            $wooCommerceOrders = json_decode($resp);
            if($wooCommerceOrders==null){
                return redirect(route('woo.orders'))->with('success','No Orders found try again');
            }
            $orders_id = array_column($wooCommerceOrders,'id');

            $record = WooCommerceOrder::whereIn('order_id',$orders_id)->where('added_by',$this->user->id)->pluck('order_id');

            $ordersArray =[];
            foreach($wooCommerceOrders as $key=>$order){
                $r = in_array($order->id,$record->toArray());
                if($r){
                    unset($wooCommerceOrders[$key]);
                }
                else{
                    $orderData = [
                        'order_id' => $order->id,
                        'order_date' => date('Y-m-d', strtotime($order->date_created)),
                        'total' => $order->total,
                        'status' => $order->status,
                        'company_id' => $this->company->id,
                        'added_by' => $this->user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $ordersArray[] = $orderData; // add the order data to the array
                }
            }

            if (!empty($ordersArray)) {
                WooCommerceOrder::insert($ordersArray);
                return redirect(route('woo.orders'))->with('success','New Orders addedd successfully');
            }
            else{
                return redirect(route('woo.orders'))->with('success','No New Orders');
            }


        }
        elseif($status_code=='401'){
            return redirect(route('woo.orders'))->with('error','Sorry, you cannot list resources.');
        }
        else{
            return redirect(route('woo.orders'))->with('error','Credentials invalid.');
        }
    }

    public function changeOrderStatus(Request $request){
        $ok = $this->updateWooCommerceOrder($request->orderId,$request->status);
        if($ok){
            $order = WooCommerceOrder::where('order_id',$request->orderId)->first();

            $order->status = $request->status;

            $order->save();
            return Reply::success(__('messages.orderStatusChanged'));
        }
        else{
            return Reply::error(__('messages.errorOccured'));
        }

    }

    public function updateWooCommerceOrder($orderId, $orderStatus){
        $wooCommerceCredential = WooCommerce::where('user_id',$this->user->id)->first();

        $url = $wooCommerceCredential->website_url.'/wp-json/wc/v3/orders/'.$orderId;

        $client_id = $wooCommerceCredential->client_id;
        $secret_key = $wooCommerceCredential->secret_key;
        $data = [
            'status' => $orderStatus
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        // curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, "$client_id:$secret_key");
        $resp = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($status_code=='200'){
            $wooCommerceOrders = json_decode($resp);
            if($wooCommerceOrders==null){
                return false;
            }
            return true;
        }
        elseif($status_code=='401'){
            return false;
            //return redirect(route('woo.create'))->with('error','Sorry, you cannot list resources.');
        }
        else{
            return false;
            //return redirect(route('woo.create'))->with('error','Something went wrong.');
        }

    }
}
