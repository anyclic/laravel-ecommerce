<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Ecommerce\Filters\OrderFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\Admin\OrderAdminResource;

class DashboardController extends Controller
{
    public function orderAnalytics(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,all'
        ]);

        $days = 7;
        $period = $request->period;
        $groupBy = "DATE('o.created_at')";
        $label = 'DATE(o.created_at) as created_at';

        switch ($period) {
            case 'week':
                $days = 7;
                $groupBy = 'DATE(o.created_at)';
                $label = 'DATE(o.created_at) as created_at';
                break;
            case 'month':
                $days = 30;
                $groupBy = 'DATE(o.created_at)';
                $label = 'DATE(o.created_at) as created_at';
                break;
            case 'year':
                $days = 365;
                $groupBy = 'MONTH(o.created_at)';
                $label = 'MONTH(o.created_at) as created_at';
                break;
            case 'all':
                $days = 365 * 5;
                $groupBy = 'YEAR(o.created_at)';
                $label = 'YEAR(o.created_at) as created_at';
                break;
        }

        $ordersChartData = DB::table('ecommerce_orders as o')->select(DB::raw($label), DB::raw('count(*) as total'))
            ->whereDate('o.created_at', '>', Carbon::now()->subDays($days))
            ->whereDate('o.created_at', '<=', Carbon::now())
            ->join('statuses as s', 'o.id', '=', 's.model_id')
            ->where('s.model_type', '=', Order::class)
            ->groupBy(DB::raw($groupBy));

        $completeOrdersChartData = clone $ordersChartData;
        $completeOrdersChartData = $completeOrdersChartData->where('s.name', 'complete')->get()->mapWithKeys(function ($item) {
            return [$item->created_at => $item->total];
        })->all();

        $otherOrdersChartData = clone $ordersChartData;
        $orderOtherChartData = $otherOrdersChartData->where('s.name', '<>', 'complete')->get()->mapWithKeys(function ($item) {
            return [$item->created_at => $item->total];
        })->all();

        $ordersChartData = $ordersChartData->get();

        $summary = [
            ["title" => "Total", "value" => Order::whereRaw("1=1")->count()],
            ["title" => "Last Month", "value" => Order::whereDate('created_at', '>', Carbon::now()->subDays(30))->count()],
            ["title" => "Last Week", "value" => Order::whereDate('created_at', '>', Carbon::now()->subDays(7))->count()],
            ["title" => "Today", "value" => Order::whereDate('created_at', '=', Carbon::now())->count()]
        ];

        $chartLabel = array_map(function ($date) use ($period) {
            if ($period == 'week') {
                $in = date_create($date);
                return date_format($in, "D");
            }
            if ($period == 'month') {
                $in = date_create($date);
                return date_format($in, "d");
            }
            if ($period == 'year') {
                $in = date_create('2013-10-27'); // create a sample date object
                $in->setDate($in->format('Y'), $date, $in->format('d'));
                return $in->format("M");
            }
            if ($period == 'all') {
                $in = date_create('2013-10-27'); // create a sample date object
                $in->setDate($date, $in->format('m'), $in->format('d'));
                return $in->format("Y");
            }
            return $date;
        }, $ordersChartData->pluck('created_at')->toArray());

        $allOrdersCount = [];
        $completeOrdersCount = [];
        $otherOrdersCount = [];

        foreach ($ordersChartData as $orderData) {
            $allOrdersCount[]  = $orderData->total;
            $completeOrdersCount[] = $completeOrdersChartData[$orderData->created_at] ?? 0;
            $otherOrdersCount[] = $orderOtherChartData[$orderData->created_at] ?? 0;
        }

        return response()->json([
            "chart" => [
                "chartLabel" => $chartLabel,
                "linesData" => [
                    $allOrdersCount,
                    $otherOrdersCount,
                    $completeOrdersCount,
                ]
            ],
            "summary" => $summary
        ]);
    }

    public function progressInfo(Request $request)
    {
        return response()->json([
            $this->revenueProgressInfo(),
            $this->ordersProgressInfo(),
            $this->distanceProgressInfo(),
        ]);
    }

    public function activeOrderLocations(Request $request)
    {
        $orders = Order::query(['active' => 1], OrderFilter::class)->latest()->limit(50);

        return OrderAdminResource::collection($orders->get());
    }

    private function revenueProgressInfo()
    {
        $today = Order::whereDate('created_at', Carbon::now())->sum('total');
        $yesterday = Order::whereDate('created_at', Carbon::now()->subDays(1))->sum('total');

        return $this->progressInfoFormat($today, $yesterday, "Revenue");
    }

    private function ordersProgressInfo()
    {
        $today = Order::whereDate('created_at', Carbon::now())->count();
        $yesterday = Order::whereDate('created_at', Carbon::now()->subDays(1))->count();

        return $this->progressInfoFormat($today, $yesterday, "Orders");
    }

    private function distanceProgressInfo()
    {
        $today = Order::whereDate('created_at', Carbon::now())->sum('distance_travelled');
        $yesterday = Order::whereDate('created_at', Carbon::now()->subDays(1))->sum('distance_travelled');

        return $this->progressInfoFormat($today, $yesterday, "Distance");
    }

    private function progressInfoFormat($today, $yesterday, $title)
    {
        $percentChange =  $today > 0 ? (($yesterday - $today) / $today) * 100 : ($yesterday > 0 ? -100 : 0);
        $percentChange = round($percentChange, 2);

        $changeDescription = "";
        if ($percentChange > 0) {
            $changeDescription = "Better than yesterday ($percentChange%)";
        } else if ($percentChange == 0) {
            $changeDescription = "No change since yesterday";
        } else if ($percentChange < 0) {
            $changeDescription = "Lesser than yesterday ($percentChange%)";
        }

        return [
            "title" => "Today's $title",
            "value" => $today,
            "activeProgress" => $percentChange,
            "description" => $changeDescription
        ];
    }
}
