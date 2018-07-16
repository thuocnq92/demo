<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\APIBaseController;
use App\Models\UserDeviceToken;
use Aws\Sns\Exception\SnsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserDeviceTokenController extends APIBaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerDeviceToken(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'platform' => 'required',
            'device_token' => 'required'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            $messages = $validate->messages();

            return $this->sendError('Validate error !', $messages, 422);
        }

        $input = $request->only('platform', 'device_token');
        try {
            $client = App::make('aws')->createClient('sns');
            $deviceToken = UserDeviceToken::whereDeviceToken($input['device_token'])->first();
            if ($deviceToken == null) {
                $platformApplicationArn = '';
                if (isset($input['platform']) && $input['platform'] == 'android') {
                    $platformApplicationArn = env('ANDROID_APPLICATION_ARN', 'arn:aws:sns:ap-southeast-1:639461452342:app/GCM/live_stream_qa');
                }

                if (isset($input['platform']) && $input['platform'] == 'ios') {
                    $platformApplicationArn = env('IOS_APPLICATION_ARN', 'arn:aws:sns:ap-southeast-1:639461452342:app/APNS/live_stream_qa');
                }

                $result = $client->createPlatformEndpoint(array(
                    'PlatformApplicationArn' => $platformApplicationArn,
                    'Token' => $input['device_token'],
                ));
                $endPointArn = isset($result['EndpointArn']) ? $result['EndpointArn'] : '';
                $deviceToken = new UserDeviceToken();
                $deviceToken->platform = $input['platform'];
                $deviceToken->device_token = $input['device_token'];
                $deviceToken->arn = $endPointArn;
                $subscriptionArn = '';
                if (isset($input['platform']) && $input['platform'] == 'android') {
                    $subscriptionArn = $this->subscribeDeviceTokenToTopic($endPointArn, env('TOPIC_LIVE_GAME', null));
                }

                if (isset($input['platform']) && $input['platform'] == 'ios') {
                    $subscriptionArn = $this->subscribeDeviceTokenToTopic($endPointArn, env('TOPIC_LIVE_GAME_IOS', null));
                }

                $deviceToken->subscription_arn = $subscriptionArn;
            } else {
                // If device_token exist but AWS SNS disabled need enabled again
                $endPointArn = $deviceToken->arn;
                $endpointAtt = $client->getEndpointAttributes(["EndpointArn" => $endPointArn]);
                if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled'] == 'false') {
                    $client->setEndpointAttributes([
                        'Attributes' => [
                            'Enabled' => 'true'
                        ],
                        'EndpointArn' => $endPointArn
                    ]);
                }
            }
            $deviceToken->user_id = $user->id;
            $deviceToken->save();
        } catch (SnsException $e) {
            Log::info($e->getMessage());

            return response()->json(['success' => false, 'message' => "Unexpected Error"], 500);
        }

        return response()->json(['success' => true, "message" => "Device token processed"], 200);
    }

    /**
     * @param $endPointArn
     * @return string
     */
    public function subscribeDeviceTokenToTopic($endPointArn, $topic_name = '')
    {
        $sns = App::make('aws')->createClient('sns');
        $result = $sns->subscribe([
            'Endpoint' => $endPointArn,
            'Protocol' => 'application',
            'TopicArn' => $topic_name,
        ]);

        return $result['SubscriptionArn'] ? $result['SubscriptionArn'] : '';
    }

    /**
     * @param $subscriptionArn
     */
    public function unsubscribeDeviceTokenToTopic($subscriptionArn)
    {
        $sns = App::make('aws')->createClient('sns');
        $sns->unsubscribe([
            'SubscriptionArn' => $subscriptionArn,
        ]);
    }

    /**
     * @param $message
     * @return string
     */
    public function publishToTopic($message)
    {
        $sns = App::make('aws')->createClient('sns');
        $result = $sns->publish([
            'Message' => $message,
            'MessageStructure' => 'json',
            'TopicArn' => env('TOPIC_LIVE_GAME', null),
        ]);

        Log::debug('Send notification:' . $message . ' to topic Android: ' . env('TOPIC_LIVE_GAME', null));

        return $result['MessageId'] ? $result['MessageId'] : '';
    }

    /**
     * @param $message
     * @return string
     */
    public function publishToTopicIOS($message)
    {
        $sns = App::make('aws')->createClient('sns');
        $result = $sns->publish([
            'Message' => $message,
            'MessageStructure' => 'json',
            'TopicArn' => env('TOPIC_LIVE_GAME_IOS', null),
        ]);

        Log::debug('Send notification:' . $message . ' to topic IOS: ' . env('TOPIC_LIVE_GAME_IOS', null));

        return $result['MessageId'] ? $result['MessageId'] : '';
    }
}
