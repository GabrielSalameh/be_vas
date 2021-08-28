<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\SubscriptionLogging;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return auth()->payload();
    }



    /**
     * Subscribe.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe()
    {
        $payload = auth()->payload()->toArray();
        $validator = Validator::make($payload, [
            'merchantID' => 'required|string',
            'subscriptionId' => 'required|string',
            'msisdn' => 'required|string',
            'operatorId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cnt = Subscriber::query()
            ->where('merchant_id', $payload['merchantID'])
            ->where('subscription_id', $payload['subscriptionId'])
            ->where('msisdn', $payload['msisdn'])
            ->where('operator_id', $payload['operatorId'])->count();

        if ($cnt > 0) {
            return response()->json([
                'success' => false
            ]);
        }

        Subscriber::query()->create([
            'merchant_id' => $payload['merchantID'],
            'subscription_id' => $payload['subscriptionId'],
            'msisdn' => $payload['msisdn'],
            'operator_id' => $payload['operatorId'],
        ]);

        SubscriptionLogging::query()->create([
            'type' => 'subscribe',
            'payload' => json_encode($payload),
            'merchant_id' => $payload['merchantID'],
            'subscription_id' => $payload['subscriptionId'],
            'msisdn' => $payload['msisdn'],
            'operator_id' => $payload['operatorId'],
        ]);

        //call the partner's api

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Unsubscribe.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Request $request)
    {
        $payload = auth()->payload()->toArray();
        $validator = Validator::make($payload, [
            'merchantID' => 'required|string',
            'subscriptionId' => 'required|string',
            'msisdn' => 'required|string',
            'operatorId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cnt = Subscriber::query()
            ->where('merchant_id', $payload['merchantID'])
            ->where('subscription_id', $payload['subscriptionId'])
            ->where('msisdn', $payload['msisdn'])
            ->where('operator_id', $payload['operatorId'])
            ->where('status', '<',2)->count();

        if ($cnt != 1) {
            return response()->json([
                'success' => false
            ]);
        }

        Subscriber::query()
            ->where('merchant_id', $payload['merchantID'])
            ->where('subscription_id', $payload['subscriptionId'])
            ->where('msisdn', $payload['msisdn'])
            ->where('operator_id', $payload['operatorId'])
            ->update(['status' => 2]);

        SubscriptionLogging::query()->create([
            'type' => 'unsubscribe',
            'payload' => json_encode($payload),
            'merchant_id' => $payload['merchantID'],
            'subscription_id' => $payload['subscriptionId'],
            'msisdn' => $payload['msisdn'],
            'operator_id' => $payload['operatorId'],
        ]);

        //call the partner's api

        return response()->json([
            'success' => true
        ]);
    }


    /**
     * Subscribe Callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe_callback()
    {
        $payload = auth()->payload()->toArray();
        //id in payload will be send to the partner in the subscribe api
        $validator = Validator::make($payload, [
            'id' => 'required|string',
            'success' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //validate that there is a pending subscribe record with this id in the subscriber table
        $subscriber = Subscriber::query()->where('status', 0)->find($payload['id']);

        if (!$subscriber) {
            return response()->json([
                'success' => false
            ]);
        }

        //in case the partners returns successful subscribe set status to subscribed else remove subscriber record (no need to keep it pending)
        if($payload['success'] == true) {
            $subscriber->status = 1;
            $subscriber->save();
        } else {
            $subscriber->delete();
        }

        SubscriptionLogging::query()->create([
            'type' => 'subscribe_callback',
            'payload' => json_encode($payload)
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Unsubscribe Callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe_callback()
    {
        $payload = auth()->payload()->toArray();
        //id in payload will be send to the partner in the unsubscribe api
        $validator = Validator::make($payload, [
            'id' => 'required|string',
            'success' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //validate that there is a pending unsubscribe record with this id in the subscriber table
        $subscriber = Subscriber::query()->where('status', 2)->find($payload['id']);

        if (!$subscriber) {
            return response()->json([
                'success' => false
            ]);
        }

        //in case the partners returns successful unsubscribe remove subscriber record else return status to subscribed
        if($payload['success'] == true) {
            $subscriber->delete();
        } else {
            $subscriber->status = 1;
            $subscriber->save();
        }

        SubscriptionLogging::query()->create([
            'type' => 'unsubscribe_callback',
            'payload' => json_encode($payload)
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
