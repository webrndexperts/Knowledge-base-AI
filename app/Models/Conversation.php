<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queries(): HasMany
    {
        return $this->hasMany(Query::class)->orderBy('created_at');
    }

    public function latestQuery()
    {
        return $this->hasOne(Query::class)->latestOfMany();
    }

    /**
     * Generate a title from the first query
     */
    public function generateTitle(): string
    {
        $firstQuery = $this->queries()->first();
        if (! $firstQuery) {
            return 'New Conversation';
        }

        $question = $firstQuery->question;

        // Limit to 50 characters and add ellipsis if needed
        return strlen($question) > 50 ? substr($question, 0, 47).'...' : $question;
    }

    /**
     * Auto-generate and save title if not set
     */
    public function ensureTitle(): void
    {
        if (empty($this->title) || $this->title === 'New Conversation') {
            $this->title = $this->generateTitle();
            $this->save();
        }
    }

    /**
     * Get encrypted conversation ID for URLs (short format with - and _)
     */
    public function getEncryptedIdAttribute(): string
    {
        return self::encryptId($this->id);
    }

    /**
     * Encrypt ID to UUID-like format (14 chars with - and _)
     * Format: xxxx-xxxx_xxxx (14 chars total)
     */
    public static function encryptId(int $id): string
    {
        // Encode the ID with a simple cipher
        $key = crc32(config('app.key')) & 0xFFFFFF; // 24-bit key
        $encoded = $id ^ $key;

        // Convert to hex and pad to 8 characters
        $hex = str_pad(dechex($encoded), 8, '0', STR_PAD_LEFT);

        // Add checksum (4 chars)
        $checksum = substr(hash('crc32', $id.config('app.key')), 0, 4);

        // Combine: 8 chars + 4 chars = 12 chars
        $combined = $hex.$checksum;

        // Format like UUID: xxxx-xxxx_xxxx (14 chars with separators)
        $formatted = substr($combined, 0, 4).'-'.substr($combined, 4, 4).'_'.substr($combined, 8, 4);

        return $formatted;
    }

    /**
     * Decrypt conversation ID from UUID-like format
     */
    public static function decryptId(string $encryptedId): ?int
    {
        try {
            // Remove separators: xxxx-xxxx_xxxx -> xxxxxxxxxxxx
            $cleaned = str_replace(['-', '_'], '', $encryptedId);

            if (strlen($cleaned) !== 12) {
                return null;
            }

            // Split hex and checksum
            $hex = substr($cleaned, 0, 8);
            $checksum = substr($cleaned, 8, 4);

            // Convert hex back to decimal
            $encoded = hexdec($hex);

            // Decrypt with XOR
            $key = crc32(config('app.key')) & 0xFFFFFF;
            $id = $encoded ^ $key;

            // Verify checksum
            $expectedChecksum = substr(hash('crc32', $id.config('app.key')), 0, 4);

            if ($checksum !== $expectedChecksum) {
                return null;
            }

            return (int) $id;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find conversation by encrypted ID for the authenticated user
     */
    public static function findByEncryptedId(string $encryptedId, int $userId): ?self
    {
        $id = self::decryptId($encryptedId);

        if (! $id) {
            return null;
        }

        return self::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }
}
