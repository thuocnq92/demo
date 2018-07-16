<?php

return [
    'validate_otp' => [
        'otp_invalid' => '認証コードが間違っています。',
        'phone_not_exist' => 'Phone not exist.',
        'user_not_found' => 'User not found !',
    ],
    'referral' => [
        'wrong_referral_code' => '招待コードが間違っています。',
    ],
    'email' => [
        'payment_request' => [
            'subject' => '【Qryppo】出金申請を受け付けました。',
            'hi' => ':name 様',
            'heading' => 'いつもQryppo（クリッポ）をご利用頂きまして、誠にありがとうございます。<br/>
                          出金申請の受け付けが完了いたしましたのでお知らせいたします。',
            'amount' => '出金額：:amount',
            'fee' => '受取額：:fee',
            'unit' => '円',
            'tip_1' => '入金まで最大で２週間程度のお時間を頂いております。',
            'tip_2' => '引き続き、Qryppo（クリッポ）をどうぞよろしくお願いいたします。',
            'signature' => '株式会社Skrum<br/>
                        お問い合わせ：support@skrum.co.jp',
        ],
    ],
];