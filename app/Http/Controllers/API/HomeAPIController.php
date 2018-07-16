<?php

namespace App\Http\Controllers\API;

use App\Models\OTP;
use App\Models\Phone;
use App\Models\User;
use Aws\Sns\Exception\SnsException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use AWS;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class HomeAPIController extends APIBaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOTP(Request $request)
    {
        $rules = [
            'phone' => 'required|numeric|digits_between:10,11'
        ];

        // Set custom message for validate
        $messages = [
            'phone.numeric' => '電話番号は半角数字を指定してください。'
        ];

        // Set custom name for japanese
        $attributeNames = array(
            'phone' => '電話番号'
        );

        $validate = Validator::make($request->all(), $rules, $messages);
        $validate->setAttributeNames($attributeNames);
        if ($validate->fails()) {
            $messages = $validate->messages();

            return $this->sendError('Validate error !', $messages, 422);
        }

        // Get Phone with phone number
        $phone = Phone::where('phone', $request->get('phone'))->first();
        if (empty($phone)) {
            $phone = Phone::create([
                'phone' => $request->get('phone'),
                'is_created' => false
            ]);
        }

        // Get OTP of user expired at 10 minutes
        $otp = OTP::where('phone_id', $phone->id)
            ->where('is_logged', OTP::NOT_LOGGED)
            ->where('expired_at', '>=', Carbon::now()->toDateTimeString())
            ->first();

        if (empty($otp)) {
            $random_code = rand(1000, 9999);

            $otp = OTP::create([
                'phone_id' => $phone->id,
                'code' => $random_code,
                'is_logged' => OTP::NOT_LOGGED,
                'expired_at' => Carbon::now()->addMinutes(OTP::TIME_OTP_EXPIRED)->toDateTimeString()
            ]);
        }

        // Send SMS
        try {
            if (App::environment('production', 'staging')) {
                $this->sendSMS($phone->phone, $otp->code);
            }
        } catch (SnsException $e) {
            return response()->json(['error' => "Unexpected Error"], 500);
        }

        $result = [
            'phone' => $phone->phone,
            'expired_at' => $otp->expired_at->timestamp
        ];

        // Add code otp for debug
        if (!App::environment('production')) {
            $result['code'] = $otp->code;
        }

        return $this->sendResponse($result, 'Create OTP success.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateOTP(Request $request)
    {
        $rules = [
            'phone' => 'required|numeric|digits_between:10,11',
            'code' => 'required|numeric|digits:4'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            $messages = $validate->messages();

            return $this->sendError('Validate error !', $messages, 422);
        }

        $phone = Phone::where('phone', $request->get('phone'))->first();
        if (empty($phone)) {
            return $this->sendError(trans('messages.validate_otp.phone_not_exist'));
        }

        $validOtp = OTP::where('phone_id', $phone->id)
            ->where('is_logged', OTP::NOT_LOGGED)
            ->where('code', $request->get('code'))
            ->where('expired_at', '>=', Carbon::now()->toDateTimeString())
            ->first();

        if (empty($validOtp)) {
            return $this->sendError(trans('messages.validate_otp.otp_invalid'), [], 400);
        }

        // Check if phone is register to login user
        if (empty($phone->is_created)) {
            // Create new user if valid OTP
            $affiliate_id = User::generate_unique_affiliate_id();
            $user = User::create([
                'phone_id' => $phone->id,
                'affiliate_id' => $affiliate_id
            ]);
            if ($user) {
                // After create new user mark phone is_created
                $phone->is_created = true;
                $phone->save();
            }
        }

        $user = User::where('phone_id', $phone->id)->first();
        if (empty($user)) {
            return $this->sendError(trans('messages.validate_otp.user_not_found'));
        }

        // Mark otp is logged
        $validOtp->is_logged = OTP::LOGGED;
        $validOtp->save();

        $token = JWTAuth::fromUser($user);

        $result = [
            'token' => $token,
            'is_new_user' => $user->is_new_user
        ];

        return $this->sendResponse($result, 'User login !');
    }

    /**
     * @param string $phone_number
     * @param string $code
     */
    protected function sendSMS($phone_number = '', $code = '')
    {
        $sms = AWS::createClient('sns');

        if (!empty($phone_number)) {
            // Detect environment to append country code
            if (App::environment('production', 'staging')) {
                $phone_number = '+81' . $phone_number;
            } else {
                $phone_number = '+84' . $phone_number;
            }

            $sms->publish([
                'Message' => '認証コード：' . $code . '
この番号をQryppoアプリの画面で入力してください。
コードの有効期限は10分です。',
                'PhoneNumber' => $phone_number,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional',
                    ]
                ],
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetToken()
    {
        $token = JWTAuth::getToken();
        try {
            $token = JWTAuth::refresh($token); // might fail
            JWTAuth::setToken($token);
            JWTAuth::authenticate($token);

            $result = [
                'token' => $token
            ];

            return $this->sendResponse($result, 'Refresh token success !');
        } catch (TokenExpiredException $e) {
            // token cannot be refreshed, user needs to login again
            return $this->sendError('Need to Login Again', [], 400);
        } catch (TokenBlacklistedException $e) {
            // Token blacklist need login again
            return $this->sendError('Token in blacklist, login again.', [], 400);
        }
    }
}
