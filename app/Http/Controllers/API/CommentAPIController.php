<?php

namespace App\Http\Controllers\API;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Responses\Api;

class CommentAPIController extends APIBaseController
{
    /**
     * Set model
     * 
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->Comment = $comment;
    }

    /**
     * Get comment of user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComment()
    {
        $user = Auth::user();
        if (empty($user)) {
            $this->sendError('User not found');
        }
        $comment = $this->Comment->where('user_id', $user->id)->first();
        if (empty($comment)) {
            return Api::error('E0001');
        }
        $data = [
            'user_id' => $comment->user_id,
            'content' => $comment->content,
        ];
        return Api::success($data, 'M0001');
    }

    /**
     * 
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeComment(Request $request)
    {
        $user = Auth::user();
        $codeMessage = 'M0003';
        if (empty($user)) {
            $this->sendError('User not found');
        }
        $rules = [
            'content' => 'required|max:100',
        ];

        // Set custom message for validate
        $messages = [
            'content.required' => 'コメントを入力してください。'
        ];

        // Set custom name for japanese
        $attributeNames = array(
            'name' => 'コメント'
        );

        $validate = Validator::make($request->all(), $rules, $messages);
        $validate->setAttributeNames($attributeNames);
        if ($validate->fails()) {
            $messages = $validate->messages();
            return $this->sendError('Validate error !', $messages, 422);
        }
        $comment = $this->Comment->where('user_id', $user->id)->first();
        if(empty($comment)) {
            $codeMessage = 'M0002';
            $comment = $this->Comment;
            $comment->user_id = $user->id;
        }
        $comment->content = $request->get('content');
        
        if ($comment->save()) {
            $data = [
                'user_id' => $comment->user_id,
                'content' => $comment->content,
            ];
            return Api::success($data, $codeMessage);
        }
    }
}
