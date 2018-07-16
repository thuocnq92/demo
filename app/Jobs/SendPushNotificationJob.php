<?php

namespace App\Jobs;

use App\Http\Controllers\API\UserDeviceTokenController;
use App\Models\Notification;
use App\Models\UserDeviceToken;
use Aws\Sns\Exception\SnsException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->notification) {
            $notificationTitle = "Live Game is online !";
            $notificationMessage = $this->notification->content;
            $data = [
                "type" => "live_game",
                "game_id" => $this->notification->game_id
            ];

            // Create message for android
            $fcmPayload = json_encode(
                [
                    "notification" =>
                        [
                            "title" => $notificationTitle,
                            "body" => $notificationMessage,
                            "sound" => 'default'
                        ],
                    "data" => $data // data key is used for sending content through notification.
                ]
            );
            $message_android = json_encode(["default" => $notificationMessage, "GCM" => $fcmPayload]);

            // Create message for ios
            $iosPayload = json_encode(
                [
                    "aps" => [
                        "alert" => $notificationMessage
                    ],
                    "data" => $data
                ]
            );
            $message_ios = json_encode(["default" => $notificationMessage, "APNS" => $iosPayload]);

            try {
                if (env('SENT_TOPIC_ARN', false)) {
                    $token_controller = new UserDeviceTokenController();

                    // Send message to topic android
                    $token_controller->publishToTopic($message_android);

                    // Send message to topic ios
                    $token_controller->publishToTopicIOS($message_ios);
                } else {
                    $userDeviceTokens = UserDeviceToken::get();
                    foreach ($userDeviceTokens as $userDeviceToken) {
                        $deviceToken = $userDeviceToken->device_token;
                        $endPointArn = array("EndpointArn" => $userDeviceToken->arn);

                        $sns = App::make('aws')->createClient('sns');
                        $endpointAtt = $sns->getEndpointAttributes($endPointArn);
                        if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled'] != 'false') {
                            if ($userDeviceToken->platform == 'android') {
                                $sns->publish([
                                    'TargetArn' => $userDeviceToken->arn,
                                    'Message' => $message_android,
                                    'MessageStructure' => 'json'
                                ]);

                                Log::debug('Send notification:' . $message_android . ' to device Android: ' . $userDeviceToken->arn);
                            }

                            if ($userDeviceToken->platform == 'ios') {

                                $sns->publish([
                                    'TargetArn' => $userDeviceToken->arn,
                                    'Message' => $message_ios,
                                    'MessageStructure' => 'json'
                                ]);

                                Log::debug('Send notification:' . $message_ios . ' to device IOS: ' . $userDeviceToken->arn);
                            }
                        }

                    }
                }

                // Mark notification is sent
                $this->notification->is_sent = Notification::IS_SENT;
                $this->notification->save();
            } catch (SnsException $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
