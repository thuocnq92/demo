<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;

class RankingAPIController extends APIBaseController
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function getTotalAmountRanks(Request $request)
    {
        $limit = 100;
        if ($request->has('limit') && !empty($request->get('limit'))) {
            $limit = intval(str_replace(" ", "", $request->get('limit')));
        }

        $rankings = [];
        $sort_users = User::isActived()
            ->orderBy('total_amount', 'DESC')
            ->limit($limit)
            ->get();
        if (count($sort_users)) {
            $i = 1;
            foreach ($sort_users as $sort_user) {
                $rankings[] = [
                    'total_amount_ranking' => $i,
                    'avatar' => $sort_user->avatar,
                    'name' => $sort_user->name,
                    'total_amount' => $sort_user->total_amount
                ];
                $i++;
            }
        }

        $result = [
            'rankings' => $rankings
        ];

        return $this->sendResponse($result, 'Get list Rankings Total Amount success.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSevenDaysAmountRanks(Request $request)
    {
        $limit = 100;
        if ($request->has('limit') && !empty($request->get('limit'))) {
            $limit = intval(str_replace(" ", "", $request->get('limit')));
        }

        $rankings = [];
        $sort_users = User::isActived()
            ->orderBy('seven_days_amount', 'DESC')
            ->limit($limit)
            ->get();
        if (count($sort_users)) {
            $i = 1;
            foreach ($sort_users as $sort_user) {
                $rankings[] = [
                    'seven_days_amount_ranking' => $i,
                    'avatar' => $sort_user->avatar,
                    'name' => $sort_user->name,
                    'seven_days_amount' => $sort_user->seven_days_amount
                ];
                $i++;
            }
        }

        $result = [
            'rankings' => $rankings
        ];

        return $this->sendResponse($result, 'Get Rankings Seven days Amount success.');
    }
}
