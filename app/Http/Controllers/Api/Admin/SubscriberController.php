<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\SubscribersExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Excel;

class SubscriberController extends Controller
{
    /**
     * Display a listing of subscribers.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Subscriber::query();

        if ($search) {
            $query->where('email', 'like', "%{$search}%");
        }

        $subscribers = $query->latest()->paginate(10);

        return response()->json([
            'data' => SubscriberResource::collection($subscribers),
            'meta' => [
                'current_page' => $subscribers->currentPage(),
                'last_page' => $subscribers->lastPage(),
                'per_page' => $subscribers->perPage(),
                'total' => $subscribers->total(),
            ],
        ]);
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        return response()->json([
            'message' => 'Subscriber deleted successfully',
        ]);
    }

    /**
     * Export subscribers.
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $extension = $format === 'csv' ? Excel::CSV : Excel::XLSX;
        $fileName = 'subscribers.' . $format;

        return ExcelFacade::download(new SubscribersExport, $fileName, $extension);
    }
}
