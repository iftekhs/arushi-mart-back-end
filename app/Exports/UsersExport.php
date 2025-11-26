<?php

namespace App\Exports;

use App\Enums\UserRole;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::where('role', UserRole::USER)
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Status',
            'Total Orders',
            'Total Spent',
            'Average Order Value',
            'Joined Date',
        ];
    }

    public function map($user): array
    {
        $ordersCount = $user->orders_count ?? 0;
        $totalSpent = $user->orders_sum_total_amount ?? 0;
        $averageOrderValue = $ordersCount > 0 ? $totalSpent / $ordersCount : 0;

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->email_verified_at ? 'Active' : 'Inactive',
            $ordersCount,
            number_format($totalSpent, 2),
            number_format($averageOrderValue, 2),
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
