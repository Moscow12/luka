<?php

namespace App\Livewire\Procurement;

use App\Models\{StoreOrder, StoreRequisition, StoreOrderItem, StoreRequisitionItem};
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $todayStats = [];
    public $monthlyTrend = [];
    public $topRequestedItems = [];
    public $recentOrders = [];
    public $recentRequisitions = [];
    public $statusBreakdown = [];
    public $departmentBreakdown = [];

    public function mount()
    {
        $this->loadTodayStats();
        $this->loadMonthlyTrend();
        $this->loadTopRequestedItems();
        $this->loadRecentOrders();
        $this->loadRecentRequisitions();
        $this->loadStatusBreakdown();
        $this->loadDepartmentBreakdown();
    }

    private function loadTodayStats()
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        // Today's orders
        $todayOrders = StoreOrder::whereDate('created_at', $today)->count();
        $todayRequisitions = StoreRequisition::whereDate('created_at', $today)->count();

        $pendingOrders = StoreOrder::where('status', 'pending')->count();
        $pendingRequisitions = StoreRequisition::where('status', 'pending')->count();

        // This month
        $monthOrders = StoreOrder::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth])->count();
        $monthRequisitions = StoreRequisition::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth])->count();

        // Month items count
        $monthOrderItems = StoreOrderItem::whereHas('order', function($q) use ($thisMonth) {
            $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth]);
        })->sum(DB::raw('units * itemperunit'));

        $monthReqItems = StoreRequisitionItem::whereHas('requisition', function($q) use ($thisMonth) {
            $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth]);
        })->sum('quantity');

        $this->todayStats = [
            'today_orders' => $todayOrders,
            'today_requisitions' => $todayRequisitions,
            'pending_orders' => $pendingOrders,
            'pending_requisitions' => $pendingRequisitions,
            'month_orders' => $monthOrders,
            'month_requisitions' => $monthRequisitions,
            'month_order_items' => $monthOrderItems,
            'month_req_items' => $monthReqItems,
        ];
    }

    private function loadMonthlyTrend()
    {
        $ordersData = StoreOrder::whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $requisitionsData = StoreRequisition::whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'month' => $i,
                'orders' => $ordersData->get($i)?->count ?? 0,
                'requisitions' => $requisitionsData->get($i)?->count ?? 0,
            ];
        }

        $this->monthlyTrend = array_slice($months, 0, now()->month);
    }

    private function loadTopRequestedItems()
    {
        // Combine order items and requisition items
        $orderItems = StoreOrderItem::query()
            ->whereHas('order', function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->select(
                'item_id',
                DB::raw('SUM(units * itemperunit) as total_quantity')
            )
            ->groupBy('item_id');

        $reqItems = StoreRequisitionItem::query()
            ->whereHas('requisition', function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->select(
                'item_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('item_id');

        // Union and get top items
        $this->topRequestedItems = DB::table(DB::raw("({$orderItems->toSql()} UNION ALL {$reqItems->toSql()}) as combined"))
            ->mergeBindings($orderItems->getQuery())
            ->mergeBindings($reqItems->getQuery())
            ->join('items', 'items.id', 'combined.item_id')
            ->select(
                'items.id',
                'items.name',
                'items.code',
                'items.unit',
                DB::raw('SUM(combined.total_quantity) as total_qty')
            )
            ->groupBy('items.id', 'items.name', 'items.code', 'items.unit')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    private function loadRecentOrders()
    {
        $this->recentOrders = StoreOrder::with(['dept_ordering', 'requester'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    private function loadRecentRequisitions()
    {
        $this->recentRequisitions = StoreRequisition::with(['dept_requesting', 'dept_issuing', 'requester'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    private function loadStatusBreakdown()
    {
        $orderStatuses = StoreOrder::select('status', DB::raw('COUNT(*) as count'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $reqStatuses = StoreRequisition::select('status', DB::raw('COUNT(*) as count'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $this->statusBreakdown = [
            'orders' => [
                'pending' => $orderStatuses->get('pending')?->count ?? 0,
                'approved' => $orderStatuses->get('approved')?->count ?? 0,
                'rejected' => $orderStatuses->get('rejected')?->count ?? 0,
            ],
            'requisitions' => [
                'pending' => $reqStatuses->get('pending')?->count ?? 0,
                'approved' => $reqStatuses->get('approved')?->count ?? 0,
                'rejected' => $reqStatuses->get('rejected')?->count ?? 0,
            ],
        ];
    }

    private function loadDepartmentBreakdown()
    {
        $this->departmentBreakdown = StoreOrder::query()
            ->join('subdepartments', 'subdepartments.id', 'storeorders.dept_ordering_id')
            ->whereMonth('storeorders.created_at', now()->month)
            ->whereYear('storeorders.created_at', now()->year)
            ->select(
                'subdepartments.name',
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('subdepartments.id', 'subdepartments.name')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();
    }

    public function getMonthsProperty()
    {
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        return array_slice($months, 0, now()->month);
    }

    public function render()
    {
        return view('livewire.procurement.dashboard');
    }
}
