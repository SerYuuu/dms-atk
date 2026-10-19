<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    use HasFactory;

    // subtotal adalah generated column di database (quantity * unit_price),
    // jadi tidak boleh diisi manual.
    protected $fillable = [
        'document_id',
        'item_name',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
