<?php

declare(strict_types=1);

namespace Framework\Database;

use Doctrine\ORM\QueryBuilder;

/**
 * Helper class for configuring Doctrine eager loading to prevent N+1 queries
 */
class EagerLoadingHelper
{
    /**
     * Add eager loading (JOIN FETCH) to a query builder for specified associations
     * 
     * @param QueryBuilder $qb The query builder
     * @param string $alias The root entity alias
     * @param array $associations Array of association paths to eager load
     * @return QueryBuilder The modified query builder
     * 
     * @example
     * $qb = $repository->createQueryBuilder('u');
     * EagerLoadingHelper::addEagerLoading($qb, 'u', ['profile', 'orders', 'orders.items']);
     */
    public static function addEagerLoading(
        QueryBuilder $qb,
        string $alias,
        array $associations
    ): QueryBuilder {
        foreach ($associations as $association) {
            // Handle nested associations (e.g., 'orders.items')
            $parts = explode('.', $association);
            $currentAlias = $alias;
            $path = '';
            
            foreach ($parts as $index => $part) {
                $path .= ($path ? '.' : '') . $part;
                $joinAlias = self::generateJoinAlias($path);
                
                // Add LEFT JOIN FETCH
                $qb->leftJoin($currentAlias . '.' . $part, $joinAlias);
                $qb->addSelect($joinAlias);
                
                $currentAlias = $joinAlias;
            }
        }
        
        return $qb;
    }

    /**
     * Create a query builder with eager loading configured
     * 
     * @param QueryBuilder $qb The query builder
     * @param string $alias The root entity alias
     * @param array $associations Array of association paths to eager load
     * @param array $criteria Optional WHERE criteria
     * @return QueryBuilder The configured query builder
     * 
     * @example
     * $qb = EagerLoadingHelper::createEagerQuery(
     *     $repository->createQueryBuilder('u'),
     *     'u',
     *     ['profile', 'orders'],
     *     ['u.active' => true]
     * );
     */
    public static function createEagerQuery(
        QueryBuilder $qb,
        string $alias,
        array $associations,
        array $criteria = []
    ): QueryBuilder {
        // Add eager loading
        self::addEagerLoading($qb, $alias, $associations);
        
        // Add criteria
        foreach ($criteria as $field => $value) {
            $paramName = str_replace('.', '_', $field);
            $qb->andWhere($field . ' = :' . $paramName)
               ->setParameter($paramName, $value);
        }
        
        return $qb;
    }

    /**
     * Add batch fetching hint to a query to optimize collection loading
     * 
     * @param QueryBuilder $qb The query builder
     * @param int $batchSize The batch size (default: 100)
     * @return QueryBuilder The modified query builder
     */
    public static function addBatchFetching(
        QueryBuilder $qb,
        int $batchSize = 100
    ): QueryBuilder {
        $query = $qb->getQuery();
        $query->setHint('doctrine.fetchMode', 'EAGER');
        $query->setHint('doctrine.batchSize', $batchSize);
        
        return $qb;
    }

    /**
     * Configure partial object loading for specific fields
     * Useful for list views where you don't need all entity data
     * 
     * @param QueryBuilder $qb The query builder
     * @param string $alias The entity alias
     * @param array $fields Array of field names to select
     * @return QueryBuilder The modified query builder
     * 
     * @example
     * $qb = EagerLoadingHelper::selectPartial($qb, 'u', ['id', 'name', 'email']);
     */
    public static function selectPartial(
        QueryBuilder $qb,
        string $alias,
        array $fields
    ): QueryBuilder {
        // Build partial select
        $select = 'PARTIAL ' . $alias . '.{' . implode(', ', $fields) . '}';
        $qb->select($select);
        
        return $qb;
    }

    /**
     * Add pagination with eager loading
     * 
     * @param QueryBuilder $qb The query builder
     * @param int $page The page number (1-indexed)
     * @param int $perPage Items per page
     * @return QueryBuilder The modified query builder
     */
    public static function addPagination(
        QueryBuilder $qb,
        int $page,
        int $perPage = 20
    ): QueryBuilder {
        $offset = ($page - 1) * $perPage;
        
        $qb->setFirstResult($offset)
           ->setMaxResults($perPage);
        
        return $qb;
    }

    /**
     * Get optimization suggestions based on query analysis
     * 
     * @param array $queryStats Query statistics from QueryLogger
     * @return array Array of optimization suggestions
     */
    public static function getOptimizationSuggestions(array $queryStats): array
    {
        $suggestions = [];
        
        // Check for high query count
        if ($queryStats['total_queries'] > 50) {
            $suggestions[] = [
                'type' => 'high_query_count',
                'severity' => 'high',
                'message' => sprintf(
                    'High query count detected (%d queries). Consider using eager loading with JOIN FETCH.',
                    $queryStats['total_queries']
                ),
                'example' => 'EagerLoadingHelper::addEagerLoading($qb, \'entity\', [\'association1\', \'association2\'])',
            ];
        }
        
        // Check for N+1 problems
        if ($queryStats['potential_n_plus_one'] > 0) {
            $suggestions[] = [
                'type' => 'n_plus_one',
                'severity' => 'critical',
                'message' => sprintf(
                    'Potential N+1 query problems detected (%d patterns). Use eager loading to fetch related entities in a single query.',
                    $queryStats['potential_n_plus_one']
                ),
                'example' => 'EagerLoadingHelper::createEagerQuery($qb, \'entity\', [\'relatedEntity\'])',
            ];
        }
        
        // Check for slow queries
        if ($queryStats['slow_queries'] > 0) {
            $suggestions[] = [
                'type' => 'slow_queries',
                'severity' => 'medium',
                'message' => sprintf(
                    '%d slow queries detected (>100ms). Consider adding database indexes or optimizing query logic.',
                    $queryStats['slow_queries']
                ),
                'example' => 'Add indexes to frequently queried columns or use partial object loading for list views.',
            ];
        }
        
        // Check for high average duration
        if ($queryStats['average_duration'] > 0.05) {
            $suggestions[] = [
                'type' => 'high_average_duration',
                'severity' => 'medium',
                'message' => sprintf(
                    'High average query duration (%.2fms). Review query complexity and database indexes.',
                    $queryStats['average_duration'] * 1000
                ),
                'example' => 'Use EXPLAIN to analyze query execution plans and add appropriate indexes.',
            ];
        }
        
        return $suggestions;
    }

    /**
     * Generate a unique alias for a join based on the association path
     */
    private static function generateJoinAlias(string $path): string
    {
        return str_replace('.', '_', $path);
    }

    /**
     * Create a query with common optimizations applied
     * 
     * @param QueryBuilder $qb The query builder
     * @param string $alias The root entity alias
     * @param array $config Configuration array with keys:
     *   - associations: array of associations to eager load
     *   - partial: array of fields for partial loading
     *   - criteria: array of WHERE criteria
     *   - page: page number for pagination
     *   - perPage: items per page
     *   - batchSize: batch size for batch fetching
     * @return QueryBuilder The optimized query builder
     */
    public static function optimize(
        QueryBuilder $qb,
        string $alias,
        array $config = []
    ): QueryBuilder {
        // Apply eager loading
        if (!empty($config['associations'])) {
            self::addEagerLoading($qb, $alias, $config['associations']);
        }
        
        // Apply partial loading
        if (!empty($config['partial'])) {
            self::selectPartial($qb, $alias, $config['partial']);
        }
        
        // Apply criteria
        if (!empty($config['criteria'])) {
            foreach ($config['criteria'] as $field => $value) {
                $paramName = str_replace('.', '_', $field);
                $qb->andWhere($field . ' = :' . $paramName)
                   ->setParameter($paramName, $value);
            }
        }
        
        // Apply pagination
        if (isset($config['page'])) {
            $perPage = $config['perPage'] ?? 20;
            self::addPagination($qb, $config['page'], $perPage);
        }
        
        // Apply batch fetching
        if (isset($config['batchSize'])) {
            self::addBatchFetching($qb, $config['batchSize']);
        }
        
        return $qb;
    }
}
