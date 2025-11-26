<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    /**
     * Get all customers (users with role USER) for admin panel
     */
    public function index(Request $request): JsonResource
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $query = User::where('role', UserRole::USER)
            ->withCount('orders')
            ->withSum('orders', 'total_amount');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return UserResource::collection($customers);
    }
}
