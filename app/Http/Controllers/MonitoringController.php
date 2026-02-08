<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\ApplicationMetric;
use App\Services\MetricsCollectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    protected MetricsCollectorService $metricsCollector;

    public function __construct(MetricsCollectorService $metricsCollector)
    {
        $this->middleware('auth');
        $this->metricsCollector = $metricsCollector;
    }

    /**
     * Display the monitoring dashboard.
     */
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam;
        $servers = $team->servers()->with('sites')->get();

        // Get metrics for all servers
        $serverMetrics = [];
        foreach ($servers as $server) {
            $serverMetrics[$server->id] = $this->metricsCollector->getServerSummary($server);
        }

        return view('monitoring.index', compact('servers', 'serverMetrics'));
    }

    /**
     * Show metrics for a specific server.
     */
    public function show(Request $request, Server $server)
    {
        $this->authorize('view', $server);

        $period = $request->get('period', '24h');
        [$start, $end] = $this->getPeriodRange($period);

        // Get server summary
        $summary = $this->metricsCollector->getServerSummary($server, $this->getPeriodHours($period));

        // Get time-series data for charts
        $charts = [];
        foreach ([ApplicationMetric::TYPE_CPU, ApplicationMetric::TYPE_MEMORY, ApplicationMetric::TYPE_DISK] as $type) {
            $charts[$type] = $this->metricsCollector->getTimeSeriesData($server, $type, $start, $end);
        }

        return view('monitoring.show', compact('server', 'summary', 'charts', 'period'));
    }

    /**
     * Collect metrics for a server (manual trigger).
     */
    public function collect(Server $server)
    {
        $this->authorize('manage', $server);

        $metrics = $this->metricsCollector->collectServerMetrics($server);

        return redirect()->back()->with('success', 'Metrics collected successfully');
    }

    /**
     * Get API metrics data for charts.
     */
    public function metrics(Request $request, Server $server)
    {
        $this->authorize('view', $server);

        $metricType = $request->get('type', ApplicationMetric::TYPE_CPU);
        $period = $request->get('period', '24h');
        [$start, $end] = $this->getPeriodRange($period);

        $data = $this->metricsCollector->getTimeSeriesData($server, $metricType, $start, $end);

        return response()->json($data);
    }

    /**
     * Get period range based on string.
     */
    protected function getPeriodRange(string $period): array
    {
        return match ($period) {
            '1h' => [now()->subHour(), now()],
            '24h' => [now()->subDay(), now()],
            '7d' => [now()->subDays(7), now()],
            '30d' => [now()->subDays(30), now()],
            default => [now()->subDay(), now()],
        };
    }

    /**
     * Get period in hours.
     */
    protected function getPeriodHours(string $period): int
    {
        return match ($period) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };
    }
}
