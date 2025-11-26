<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request): JsonResource
    {
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

        $customers = $query->orderBy('created_at', 'desc')->paginate(12);
        $totalCustomers = User::where('role', UserRole::USER)->count();

        return UserResource::collection($customers)->additional([
            'meta' => [
                'total_customers' => $totalCustomers,
            ],
        ]);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $user->update([
            'status' => !$user->status
        ]);

        return $this->ok('User status updated successfully.');
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $extension = $format === 'csv' ? Excel::CSV : Excel::XLSX;
        $fileName = 'customers.' . $format;

        return ExcelFacade::download(new UsersExport, $fileName, $extension);
    }
}
