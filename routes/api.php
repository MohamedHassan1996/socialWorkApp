<?php

use App\Http\Controllers\Api\V1\Auth\AuthLoginController;
use App\Http\Controllers\Api\V1\Auth\AuthLogoutController;
use App\Http\Controllers\Api\V1\Auth\AuthRegisterController;
use App\Http\Controllers\Api\V1\Auth\ForgetPasswordOtpController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Otp\OtpController;
use App\Http\Controllers\Api\V1\Select\SelectController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\Workspace\ChangeWorkspcaeMemberRoleController;
use App\Http\Controllers\Api\V1\Workspace\PostCommentController;
use App\Http\Controllers\Api\V1\Workspace\PostController;
use App\Http\Controllers\Api\V1\Workspace\WorkspaceAttributeController;
use App\Http\Controllers\Api\V1\Workspace\WorkspaceController;
use App\Http\Controllers\Api\V1\Workspace\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;


Route::middleware(['checkLocale'])->prefix('v1/{locale}')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', AuthRegisterController::class);
        Route::post('login', AuthLoginController::class);
        Route::post('logout', AuthLogoutController::class);
    });

    Route::prefix('auth/otp-forget-password')->group(function () {
        Route::post('', [ForgetPasswordOtpController::class, 'store']);
        Route::put('', [ForgetPasswordOtpController::class, 'update']);
    });

    Route::prefix('auth/change-password')->group(function () {
        Route::put('', [ResetPasswordController::class, 'update']);
    });

    Route::prefix('otp')->group(function () {
        Route::get('verify', [OtpController::class, 'verify']);
    });

    Route::prefix('workspaces')->group(function () {
        Route::get('', [WorkspaceController::class, 'index']);
        Route::post('', [WorkspaceController::class, 'store']);
        Route::get('{workspace}', [WorkspaceController::class, 'show']);
        Route::delete('{workspace}', [WorkspaceController::class, 'destroy']);
        Route::put('{workspace}/update-name', [WorkspaceAttributeController::class, 'updateName']);
        Route::put('{workspace}/update-path', [WorkspaceAttributeController::class, 'updatePath']);
    });



    Route::prefix('workspace-members')->group(function () {
        Route::get('', [WorkspaceMemberController::class, 'index']);
        Route::post('add-member', [WorkspaceMemberController::class, 'addMember']);
        Route::delete('{workspaceMember}', [WorkspaceMemberController::class, 'destroy']);
    });

    Route::prefix('workspace-members')->group(function () {
        Route::put('{workspaceMember}/change-role', [ChangeWorkspcaeMemberRoleController::class, 'changeRole']);
    });

    Route::prefix('posts')->group(function () {
        Route::get('', [PostController::class, 'index']);
        Route::post('', [PostController::class, 'store']);
        Route::get('{post}', [PostController::class, 'show']);
        Route::put('{post}', [PostController::class, 'update']);
    });

    Route::prefix('post-comments')->group(function () {
        Route::get('', [PostCommentController::class, 'index']);
        Route::post('', [PostCommentController::class, 'store']);
        Route::put('{postComment}', [PostController::class, 'update']);
        Route::delete('{postComment}', [PostCommentController::class, 'destroy']);
    });


    Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscription/suspend', [SubscriptionController::class, 'suspend']);
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::get('/subscription/feature/{featureKey}', [SubscriptionController::class, 'featureUsage']);

    Route::prefix('selects')->group(function(){
        Route::get('', [SelectController::class, 'getSelects']);
    });



});
