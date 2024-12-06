<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $guarded = [];

    /**
     * User created
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * User updated
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    
    /**
     * Get data kategori
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori()
    {
        return $this->belongsTo(BarangKategori::class, 'kategori_id', 'id');
    }

    /**
     * List data transaksi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transaksi()
    {
        return $this->hasMany(BarangTransaksi::class, 'barang_id', 'id');
    }

    /**
     * List data stock
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stok()
    {
        return $this->hasMany(BarangStok::class, 'barang_id', 'id');
    }
}
