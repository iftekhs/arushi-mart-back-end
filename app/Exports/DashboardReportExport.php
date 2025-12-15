<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DashboardReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $summary;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->calculateSummary();
    }

    protected function calculateSummary()
    {
        $orders = Order::whereBetween('created_at', [$this->startDate, $this->endDate])->get();
        
        $this->summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'average_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
        ];
    }

    public function collection()
    {
        return Order::with(['user', 'items.variant.product', 'items.product'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['Dashboard Report'],
            ['Period: ' . $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y')],
            ['Generated: ' . now()->format('M d, Y H:i:s')],
            [],
            ['SUMMARY'],
            ['Total Orders', $this->summary['total_orders']],
            ['Total Revenue', '৳' . number_format($this->summary['total_revenue'], 2)],
            ['Average Order Value', '৳' . number_format($this->summary['average_order_value'], 2)],
            ['Pending Orders', $this->summary['pending_orders']],
            ['Processing Orders', $this->summary['processing_orders']],
            ['Completed Orders', $this->summary['completed_orders']],
            ['Cancelled Orders', $this->summary['cancelled_orders']],
            [],
            ['ORDER DETAILS'],
            ['Order ID', 'Customer', 'Email', 'Date', 'Status', 'Payment Method', 'Shipping Method', 'Items', 'Subtotal', 'Shipping', 'Total'],
        ];
    }

    public function map($order): array
    {
        $itemsList = $order->items->map(function ($item) {
            $productName = $item->product ? $item->product->name : 'Unknown Product';
            return $productName . ' (' . $item->quantity . 'x)';
        })->join(', ');

        return [
            $order->id,
            $order->user->name ?? 'Guest',
            $order->user->email ?? $order->email,
            $order->created_at->format('M d, Y H:i'),
            ucfirst($order->status->value),
            ucfirst(str_replace('_', ' ', $order->payment_method->value)),
            ucfirst(str_replace('_', ' ', $order->shipping_method->value)),
            $itemsList,
            '৳' . number_format($order->total_amount - $order->shipping_cost, 2),
            '৳' . number_format($order->shipping_cost, 2),
            '৳' . number_format($order->total_amount, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true]],
            3 => ['font' => ['italic' => true, 'size' => 9]],
            5 => ['font' => ['bold' => true, 'size' => 14]],
            14 => ['font' => ['bold' => true, 'size' => 14]],
            15 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
