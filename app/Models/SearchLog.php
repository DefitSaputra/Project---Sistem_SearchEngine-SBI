<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'results_count',
        'search_time',
        'filters',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'filters' => 'array',
        'search_time' => 'decimal:3',
    ];

    /**
     * Get the user that made the search.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get popular search queries.
     */
    public static function getPopularQueries($limit = 10)
    {
        return static::select('query')
            ->selectRaw('COUNT(*) as search_count')
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent searches for a user.
     */
    public static function getRecentSearches($userId, $limit = 10)
    {
        return static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}