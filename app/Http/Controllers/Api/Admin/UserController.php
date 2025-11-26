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
        $perPage = $request->input('per_page', 12);
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
        $totalCustomers = User::where('role', UserRole::USER)->count();

        return UserResource::collection($customers)->additional([
            'meta' => [
                'total_customers' => $totalCustomers,
            ],
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user): JsonResource
    {
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
        } else {
            $user->update(['email_verified_at' => now()]);
        }

        // Reload the user with necessary counts for the resource
        $user->loadCount('orders');
        $user->loadSum('orders', 'total_amount');

        return new UserResource($user);
    }
}
