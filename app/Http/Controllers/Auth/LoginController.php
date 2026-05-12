<?php

namespace App\Http\Controllers\Auth;


use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Libraries\AppLibrary;
use App\Models\SubSession;
use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\DefaultAccessService;
use App\Services\MenuService;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Smartisan\Settings\Facades\Settings;

class LoginController extends Controller
{
    public string $token;
    public DefaultAccessService $defaultAccessService;
    public PermissionService $permissionService;
    public MenuService $menuService;
    public ActivityLoggerService $activityLogger;

    public function __construct(
        MenuService $menuService,
        PermissionService $permissionService,
        DefaultAccessService $defaultAccessService,
        ActivityLoggerService $activityLogger
    ) {
        $this->menuService          = $menuService;
        $this->permissionService    = $permissionService;
        $this->defaultAccessService = $defaultAccessService;
        $this->activityLogger       = $activityLogger;
    }

    /**
     * @throws \Exception
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors()
            ], 422);
        }

        $request->merge(['status' => Status::ACTIVE]);

        if (!Auth::guard('web')->attempt($request->only('email', 'password', 'status'))) {
            return new JsonResponse([
                'errors' => ['validation' => 'Invalid credentials or you are blocked']
            ], 400);
        }

        // Log successful login activity
        $user = Auth::user();
        $this->activityLogger->logAuthentication('logged in', $user, [
            'guard' => 'web',
            'remember' => false,
        ]);

        $branchId = Auth::user()->branch_id;
        if (Auth::user()->branch_id == 0) {
            $branchId = Settings::group('site')->get('site_default_branch');
        }
        $this->defaultAccessService->storeOrUpdate(['branch_id' => $branchId]);
        $user        = User::where('email', $request['email'])->first();
        $this->token = $user->createToken('auth_token')->plainTextToken;

        if (!isset($user->roles[0])) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.role_exist')]
            ], 400);
        }

        $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
        $defaultPermission = AppLibrary::defaultPermission($permission); 

        return new JsonResponse([
            // 'message'           => trans('all.message.login_success'),
            'message'           => 'Login successful', 
            'token'             => $this->token,
            'branch_id'         => (int)$user->branch_id,
            'user'              => new UserResource($user),
            'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
            'permission'        => $permission,
            'defaultPermission' => $defaultPermission,
            //TODO: will remove this in the future, just for front-end development
            'massageTasks'      => SubSession::whereHas('sessionItems', fn($q) => $q->where('therapist_id', $user->id))
                                              ->with(['sessionItems' => fn($q) => $q->where('therapist_id', $user->id)])
                                              ->get()->toArray(),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Log logout activity before token deletion
        if ($user) {
            $this->activityLogger->logAuthentication('logged out', $user, [
                'guard' => 'web',
            ]);
        }
        
        $request->user()->currentAccessToken()->delete();
        return new JsonResponse([
            // 'message' => trans('all.message.logout_success')
            'message' => 'Logout successful' 
        ], 200);
    }
}
