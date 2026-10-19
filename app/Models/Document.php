<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERSIMPAN = 'tersimpan';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'document_number',
        'entity_name',
        'document_date',
        'due_date',
        'total_amount',
        'category_id',
        'importance_level',
        'status',
        'payment_status',
        'file_path',
        'file_mime_type',
        'file_size',
        'ocr_text',
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'file_size' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeTersimpan(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERSIMPAN);
    }

    /** Tagihan belum lunas yang jatuh tempo dalam $days hari ke depan (atau sudah lewat). */
    public function scopeJatuhTempo(Builder $query, int $days = 7): Builder
    {
        return $query
            ->where('payment_status', 'belum_lunas')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays($days));
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function formattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->total_amount, 0, ',', '.');
    }
}
