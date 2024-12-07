<?php

namespace App\Http\Controllers;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Base\BaseResponse;
use App\Http\Requests\BarangTransaksiRequest;
use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\BarangTransaksi;

class BarangTransaksiController extends Controller
{
    private $model, $modelBarang, $modelBarangStok;

    public function __construct(
        BarangTransaksi $model,
        Barang $modelBarang,
        BarangStok $modelBarangStok
    )
    {
        $this->model = $model;
        $this->modelBarang = $modelBarang;
        $this->modelBarangStok = $modelBarangStok;
    }
    
    /**
     * List transaksi
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return array
     */
    public function list(Request $request)
    {
        // query
        $query = $this->model->query();
        if ($request->input('with')) {
            $query->with($request->input('with'));
        }
        $query->when($request->input('barang_id'), function ($queryB, $barangId) {
            return $queryB->where('barang_id', $barangId);
        });
        $query->when($request->input('q'), function ($query, $q) {
            return $query->where('kode_transaksi', 'LIKE', '%'.$q.'%');
        });
        $result = $query->get()->toArray();

        return BaseResponse::success(
            $result, 
            'Get list transaksi barang berhasil'
        );
    }

    /**
     * Get data transaksi
     * 
     * @param int   $id
     * @param array $params
     * 
     * @return mixed
     */
    private function getTransaksi($id, $params = [])
    {
        $transaksi = $this->model->query();
        if (isset($params['with'])) {
            $transaksi->with($params['with']);
        }
        $transaksi = $transaksi->firstWhere(['id' => $id]);

        if (!$transaksi) {
            return BaseResponse::error(
                $transaksi, 
                'Get data transaksi gagal',
                [],
                404
            );
        }

        return $transaksi;
    }

    /**
     * Proses create barang
     *
     * @param \App\Http\Requests\BarangTransaksiRequest $request
     *
     * @return array
     */
    public function store(BarangTransaksiRequest $request)
    {
        $input = $request->all();
        
        DB::beginTransaction();

        try {

            if (!isset($input['kode_transaksi']) || !$input['kode_transaksi']) {
                $tipe = $input['is_masuk'] ? 'IN' : 'OUT';
                $number = str_pad($this->model->count()+1, 3, '0', STR_PAD_LEFT);
                $input['kode_transaksi'] = 'TRX-'.$tipe.'-'.now()->format('YmdHis').'-'.$number;
            }
            
            $cekKode = $this->model->firstWhere(['kode_transaksi' => $input['kode_transaksi']]);
            if ($cekKode) {
                return BaseResponse::error(
                    null, 
                    'Tambah transaksi gagal. Kode transaksi sudah digunakan'
                );
            }

            $barang = $this->modelBarang->firstWhere(['id' => $input['barang_id']]);
            if (!$barang) {
                return BaseResponse::error(
                    null, 
                    'Tambah transaksi gagal. Barang tidak ditemukan'
                );
            }

            $sisaStok = $this->modelBarangStok->where('barang_id', $barang['id'])
                ->where('qty', '>', 0)->sum('qty');
            if (!$input['is_masuk'] && $input['qty'] > $sisaStok) {
                return BaseResponse::error(
                    null, 
                    'Tambah transaksi gagal. Stok yang dimasukan ('.$input['qty'].') melebihi yang tersedia ('.$sisaStok.')'
                );
            }
            
            // proses create transaksi
            $createTransaksi = $this->model->create([
                'created_by' => null,//Auth::user()['id'],
                'barang_id' => $input['barang_id'],
                'kode_transaksi' => $input['kode_transaksi'],
                'tanggal' => $input['tanggal'],
                'qty' => $input['qty'],
                'harga' => $barang['harga'] * $input['qty'],
                'is_masuk' => $input['is_masuk'],
                'keterangan' => $input['keterangan']
            ]);

            // jika barang masuk
            if ($createTransaksi['is_masuk']) {
                $this->modelBarangStok->create([
                    'barang_id' => $createTransaksi['barang_id'],
                    'qty' => $createTransaksi['qty'],
                    'tanggal_kadaluarsa' => $input['tanggal_kadaluarsa']
                ]);

            // jika barang keluar
            } else {

                $stok = $this->modelBarangStok->where('barang_id', $barang['id'])->get();
                $qtyInput = $createTransaksi['qty'];
                foreach ($stok as $key => $stokItem) {
                    if ($qtyInput <= 0) {
                        break;
                    }
            
                    if ($stokItem->qty >= $qtyInput) {
                        $stokItem->qty -= $qtyInput;
                        $stokItem->save();
            
                        $qtyInput = 0;
                    } else {
                        $qtyInput -= $stokItem->qty;
                        $stokItem->qty = 0;
                        $stokItem->save();
                    }
                }
            }

            // update stok barang
            $barang->update([
                'qty' => $createTransaksi['is_masuk'] ? $barang['qty'] + $createTransaksi['qty'] : 
                    $barang['qty'] - $createTransaksi['qty']
            ]);

            DB::commit();

            return BaseResponse::success(
                $createTransaksi,
                'Tambah transaksi berhasil'
            );

        } catch (Exception $e) {
            DB::rollBack();
            return BaseResponse::error(
                null, 
                'Tambah transaksi gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }

    /**
    * Get detail transaksi
    * 
    * @param \Illuminate\Http\Request $request
    * @param int                      $id
    * 
    * @return array
    */
    public function show(Request $request, $id)
    {
        $params = $request->all();
        
        try {

            $transaksi = $this->getTransaksi($id, $params);
            if (isset($transaksi['status']) && !$transaksi['status']) {
                return $transaksi;
            }

            return BaseResponse::success(
                $transaksi, 
                'Get detail transaksi berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null, 
                'Get detail transaksi gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }
}
