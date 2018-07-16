<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
    <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE"/>
    <title>Simple Transactional Email</title>
</head>

<body style="padding:0; margin:0">
<table border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
    <tr>
        <td>
            {!! trans('messages.email.payment_request.hi', ['name' => $bank->bank_owner]) !!}
        </td>
    </tr>
    <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>
    <tr>
        <td>
            {!! trans('messages.email.payment_request.heading') !!}
        </td>
    </tr>
    <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>
    <tr>
        <td>{{ trans('messages.email.payment_request.amount', ['amount' => number_format( @abs($transaction->txn_amount) )]) . trans('messages.email.payment_request.unit')}}</td>
    </tr>
    <tr>
        <td>{{ trans('messages.email.payment_request.fee', ['fee' => number_format( @abs($transaction->txn_amount) - @abs($transaction->txn_fee) )]) . trans('messages.email.payment_request.unit') }}</td>
    </tr>
    <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>
    <tr>
        <td>
            {!! trans('messages.email.payment_request.tip_1') !!}
        </td>
    </tr>
    <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>
    <tr>
        <td>
            {!! trans('messages.email.payment_request.tip_2') !!}
        </td>
    </tr>
    <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>
    <tr>
        <td>--</td>
    </tr>
    <tr>
        <td>
            {!! trans('messages.email.payment_request.signature') !!}
        </td>
    </tr>
</table>
</body>
</html>