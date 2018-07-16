<?php

namespace App\Http\Controllers\API;

use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\UserBank;
use App\Transformers\BankTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\Fractal;
use League\Fractal\Manager;

class BankAPIController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rule = [
            'bank_id' => 'required|max:7',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'required|string|max:255',
            'bank_owner' => 'required|max:255',
            'type' => 'required',
        ];

        $attribute = [
            'bank_id' => 'Bank series',
            'bank_name' => 'Bank name',
            'bank_branch' => 'Bank branch',
            'bank_owner' => 'Owner',
            'type' => 'Type',
        ];

        $validator = Validator::make($request->all(), $rule, [], $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $user = Auth::user();

        if ($user->bank != null) {
            return $this->sendError('access_forbidden', [], 403);
        }

        $bank = $user->bank()->create($request->all());

        $transformer = new BankTransformer();

        $result = [
            'bank' => $transformer->transform($bank),
        ];

        return $this->sendResponse($result, 'Add bank successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $bank = $user->bank()->find($id);

        if (!$bank) {
            return $this->sendError('Bank not found');
        }

        $transformer = new BankTransformer();

        $result = [
            'bank' => $transformer->transform($bank)
        ];

        return $this->sendResponse($result, 'Get the bank successfuly');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBank()
    {
        $user = Auth::user();
        $bank = $user->bank;

        if (!$bank) {
            return $this->sendError('Bank not found');
        }

        $transformer = new BankTransformer();

        $result = [
            'bank' => $transformer->transform($bank),
            'types' => UserBank::$types,
            //'list_bank' => $this->banks()
        ];

        return $this->sendResponse($result, 'Get the bank successfuly');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $bank = $user->bank()->find($id);

        if (!$bank) {
            return $this->sendError('Bank not found');
        }

        $rule = [
            'bank_id' => 'required|max:7',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'required|string|max:255',
            'bank_owner' => 'required|max:255',
            'type' => 'required',
        ];

        $attribute = [
            'bank_id' => 'Bank series',
            'bank_name' => 'Bank name',
            'bank_branch' => 'Bank branch',
            'bank_owner' => 'Owner',
            'type' => 'Type',
        ];

        $validator = Validator::make($request->all(), $rule, [], $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $bank->update($request->all());

        $transformer = new BankTransformer();

        $result = [
            'bank' => $transformer->transform($bank)
        ];

        return $this->sendResponse($result, 'Update bank information successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $bank = $user->bank()->find($id);

        if (!$bank) {
            return $this->sendError('Bank not found');
        }

        $bank->delete();

        return $this->sendResponse([], 'Delete successfuly');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function banks()
    {
        $banks = Bank::query()
            ->orderBy('order', 'ASC')
            ->get();

        $result = [
            'banks' => $banks
        ];

        return $this->sendResponse($result, 'Get banks successfully');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankBranches($id)
    {
        $branches = BankBranch::query()
            ->where('bank_id', $id)
            ->orderBy('name', 'ASC')
            ->get();

        $result = [
            'branches' => $branches
        ];

        return $this->sendResponse($result, 'Get branches of bank successfully');
    }
}
