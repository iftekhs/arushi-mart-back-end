<?php

namespace App\Exports;

use App\Models\Subscriber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubscribersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Subscriber::select('email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($subscriber) {
                return [
                    'email' => $subscriber->email,
                    'subscribed_date' => $subscriber->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Email',
            'Subscribed Date',
        ];
    }
}
