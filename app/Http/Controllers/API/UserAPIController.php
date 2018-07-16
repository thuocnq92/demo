<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserAPIController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInfo(Request $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            $this->sendError('User not found');
        }

        $rules = [
            'name' => 'required|max:10',
            'avatar' => 'nullable|mimes:jpeg,jpg,png'
        ];

        // Set custom message for validate
        $messages = [
            'name.required' => 'ユーザ名を入力してください。'
        ];

        // Set custom name for japanese
        $attributeNames = array(
            'name' => 'ユーザ名'
        );

        $validate = Validator::make($request->all(), $rules, $messages);
        $validate->setAttributeNames($attributeNames);
        if ($validate->fails()) {
            $messages = $validate->messages();

            return $this->sendError('Validate error !', $messages, 422);
        }

        // Update name of user
        $user->name = $request->get('name');

        // Update avatar if have file
        if ($request->hasFile('avatar')) {
            $img_path = '/avatar/';
            if ($user->avatar != null) {
                $old_image = $user->avatar;
                unlink(sprintf(public_path() . $img_path . '%s', $old_image));
            }

            $file = $request->file('avatar');
            $image_name = time() . '-' . str_slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $file->getClientOriginalExtension();

            $file->move(public_path() . $img_path, $image_name);

            $image_alter = Image::make(sprintf(public_path() . $img_path . '%s', $image_name))->resize(null, 256, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();

            $user->avatar = $image_name; // Note we add the image path to the database field before the save.
        }

        $user->save();

        $result = [
            'user' => $user
        ];

        return $this->sendResponse($result, 'Update info success.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addReferralUser(Request $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            $this->sendError('User not found');
        }

        $rules = [
            'referral_code' => 'required|numeric|digits:8'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            $messages = $validate->messages();

            return $this->sendError('Validate error !', $messages, 422);
        }

        // Check if user have referral_by
        if (!empty($user->referred_by)) {
            return $this->sendError('User belong to Referral system.', [], 400);
        }

        $user_referral = User::where('id', '!=', $user->id)->where('affiliate_id', $request->get('referral_code'))->first();
        if (empty($user_referral)) {
            return $this->sendError(trans('messages.referral.wrong_referral_code'), [], 400);
        }

        $user->referred_by = $user_referral->id;
        $user->save();

        $result = [
            'user' => $user
        ];

        return $this->sendResponse($result, 'Add Referral User success.');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvatar()
    {
        $user = Auth::user();
        if (empty($user)) {
            $this->sendError('User not found');
        }

        if ($user->avatar != null) {
            $old_image = $user->avatar;
            $img_path = '/avatar/';
            unlink(sprintf(public_path() . $img_path . '%s', $old_image));
            $user->avatar = null;
            $user->save();
        }

        $result = [
            'user' => $user
        ];

        return $this->sendResponse($result, 'Delete Avatar User success.');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail()
    {
        $user = Auth::user();
        if (empty($user) || $user->is_new_user) {
            return $this->sendError('User not found');
        }

        // Append total_amount_ranking
        $total_amount_ranking = null;
        $sort_users = User::isActived()
            ->orderBy('total_amount', 'DESC')
            ->get();
        if (count($sort_users)) {
            $collection = collect($sort_users);
            $data = $collection->where('id', $user->id);
            $total_amount_ranking = $data->keys()->first() + 1;
        }
        $user->total_amount_ranking = $total_amount_ranking;

        // Append seven_days_amount_ranking
        $seven_days_amount_ranking = null;
        $sort_users = User::isActived()
            ->orderBy('seven_days_amount', 'DESC')
            ->get();
        if (count($sort_users)) {
            $collection = collect($sort_users);
            $data = $collection->where('id', $user->id);
            $seven_days_amount_ranking = $data->keys()->first() + 1;
        }
        $user->seven_days_amount_ranking = $seven_days_amount_ranking;

        $result = [
            'user' => $user
        ];

        return $this->sendResponse($result, 'Get User Detail success.');
    }
}
