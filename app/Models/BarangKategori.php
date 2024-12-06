<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKategori extends Model
{
    use HasFactory;

    protected $table = 'barang_kategori';
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
     * List data barang
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function barang()
    {
        return $this->hasMany(Barang::class, 'kategori_id', 'id');
    }
}
