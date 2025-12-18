<?php

declare(strict_types=1);

namespace Framework\Metrics;

class MetricsCollector
{
    private array $counters = [];
    private array $gauges = [];
    private array $histograms = [];
    private array $timings = [];
    private array $customMetrics = [];

    public function increment(string $metric, array $labels = []): void
    {
        $key = $this->buildKey($metric, $labels);
        
        if (!isset($this->counters[$key])) {
            $this->counters[$key] = [
                'name' => $metric,
                'labels' => $labels,
                'value' => 0
            ];
        }
        
        $this->counters[$key]['value']++;
    }

    public function gauge(string $metric, float $value, array $labels = []): void
    {
        $key = $this->buildKey($metric, $labels);
        
        $this->gauges[$key] = [
            'name' => $metric,
            'labels' => $labels,
            'value' => $value
        ];
    }

    public function histogram(string $metric, float $value, array $labels = []): void
    {
        $key = $this->buildKey($metric, $labels);
        
        if (!isset($this->histograms[$key])) {
            $this->histograms[$key] = [
                'name' => $metric,
                'labels' => $labels,
                'values' => []
            ];
        }
        
        $this->histograms[$key]['values'][] = $value;
    }

    public function timing(string $metric, float $duration, array $labels = []): void
    {
        $this->histogram($metric, $duration, $labels);
    }

    public function getMetrics(): array
    {
        return [
            'counters' => array_values($this->counters),
            'gauges' => array_values($this->gauges),
            'histograms' => $this->processHistograms(),
            'timings' => array_values($this->timings),
        ];
    }

    public function exportPrometheus(): string
    {
        $output = [];

        // Export counters
        foreach ($this->counters as $counter) {
            $labels = $this->formatLabels($counter['labels']);
            $output[] = "# TYPE {$counter['name']} counter";
            $output[] = "{$counter['name']}{$labels} {$counter['value']}";
        }

        // Export gauges
        foreach ($this->gauges as $gauge) {
            $labels = $this->formatLabels($gauge['labels']);
            $output[] = "# TYPE {$gauge['name']} gauge";
            $output[] = "{$gauge['name']}{$labels} {$gauge['value']}";
        }

        // Export histograms
        foreach ($this->histograms as $histogram) {
            $labels = $this->formatLabels($histogram['labels']);
            $values = $histogram['values'];
            
            if (empty($values)) {
                continue;
            }

            $count = count($values);
            $sum = array_sum($values);
            
            $output[] = "# TYPE {$histogram['name']} histogram";
            $output[] = "{$histogram['name']}_count{$labels} {$count}";
            $output[] = "{$histogram['name']}_sum{$labels} {$sum}";
        }

        return implode("\n", $output) . "\n";
    }

    public function reset(): void
    {
        $this->counters = [];
        $this->gauges = [];
        $this->histograms = [];
        $this->timings = [];
        $this->customMetrics = [];
    }

    /**
     * Register a custom business metric
     * 
     * @param string $name The metric name
     * @param string $type The metric type (counter, gauge, histogram)
     * @param string $description A description of what the metric measures
     * @param array $labels Optional default labels for the metric
     */
    public function registerCustomMetric(
        string $name,
        string $type,
        string $description,
        array $labels = []
    ): void {
        $this->customMetrics[$name] = [
            'type' => $type,
            'description' => $description,
            'labels' => $labels,
        ];
    }

    /**
     * Record a custom business metric
     * 
     * @param string $name The metric name (must be registered first)
     * @param mixed $value The metric value
     * @param array $labels Optional labels for this specific measurement
     */
    public function recordCustomMetric(string $name, mixed $value, array $labels = []): void
    {
        if (!isset($this->customMetrics[$name])) {
            throw new \InvalidArgumentException("Custom metric '{$name}' is not registered");
        }

        $metric = $this->customMetrics[$name];
        $mergedLabels = array_merge($metric['labels'], $labels);

        switch ($metric['type']) {
            case 'counter':
                $this->increment($name, $mergedLabels);
                break;
            case 'gauge':
                $this->gauge($name, (float) $value, $mergedLabels);
                break;
            case 'histogram':
                $this->histogram($name, (float) $value, $mergedLabels);
                break;
            default:
                throw new \InvalidArgumentException("Invalid metric type: {$metric['type']}");
        }
    }

    /**
     * Get all registered custom metrics
     */
    public function getCustomMetrics(): array
    {
        return $this->customMetrics;
    }

    private function buildKey(string $metric, array $labels): string
    {
        ksort($labels);
        return $metric . ':' . json_encode($labels);
    }

    private function formatLabels(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        $formatted = [];
        foreach ($labels as $key => $value) {
            $formatted[] = $key . '="' . addslashes($value) . '"';
        }

        return '{' . implode(',', $formatted) . '}';
    }

    private function processHistograms(): array
    {
        $processed = [];
        
        foreach ($this->histograms as $histogram) {
            $values = $histogram['values'];
            
            if (empty($values)) {
                continue;
            }

            sort($values);
            $count = count($values);
            
            $processed[] = [
                'name' => $histogram['name'],
                'labels' => $histogram['labels'],
                'count' => $count,
                'sum' => array_sum($values),
                'min' => min($values),
                'max' => max($values),
                'avg' => array_sum($values) / $count,
                'p50' => $this->percentile($values, 50),
                'p95' => $this->percentile($values, 95),
                'p99' => $this->percentile($values, 99),
            ];
        }
        
        return $processed;
    }

    private function percentile(array $values, float $percentile): float
    {
        $count = count($values);
        $index = ($percentile / 100) * ($count - 1);
        
        if (floor($index) == $index) {
            return $values[(int) $index];
        }
        
        $lower = $values[(int) floor($index)];
        $upper = $values[(int) ceil($index)];
        $fraction = $index - floor($index);
        
        return $lower + ($upper - $lower) * $fraction;
    }
}
