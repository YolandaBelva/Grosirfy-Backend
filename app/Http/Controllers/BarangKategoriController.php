<?php

namespace App\Http\Controllers;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Base\BaseResponse;
use App\Http\Requests\BarangKategoriRequest;

use App\Models\BarangKategori;

class BarangKategoriController extends Controller
{
    private $model;

    public function __construct(BarangKategori $model)
    {
        $this->model = $model;
    }
    
    /**
     * List kategori
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
        $query->when($request->input('q'), function ($query, $q) {
            return $query->where('nama', 'LIKE', '%'.$q.'%')
                ->orWhere('keterangan', 'LIKE', '%'.$q.'%');
        });
        $result = $query->get()->toArray();

        return BaseResponse::success(
            $result, 
            'Get list kategori barang berhasil'
        );
    }

    /**
     * Get data kategori
     * 
     * @param int   $id
     * @param array $params
     * 
     * @return mixed
     */
    private function getKategori($id, $params = [])
    {
        $kategori = $this->model->query();
        if (isset($params['with'])) {
            $kategori->with($params['with']);
        }
        $kategori = $kategori->firstWhere(['id' => $id]);
        
        if (!$kategori) {
            return BaseResponse::error(
                $kategori, 
                'Get data kategori barang gagal',
                [],
                404
            );
        }

        return $kategori;
    }

    /**
     * Proses create kategori
     *
     * @param \App\Http\Requests\BarangKategoriRequest $request
     *
     * @return array
     */
    public function store(BarangKategoriRequest $request)
    {
        $input = $request->all();

        try {
            
            $create = $this->model->create([
                'created_by' => null,//Auth::user()['id'],
                'nama' => $input['nama'],
                'keterangan' => $input['keterangan'] ?? null
            ]);

            return BaseResponse::success(
                $create,
                'Tambah kategori barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null, 
                'Tambah kategori barang gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }

    /**
     * Get detail kategori
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

            $kategori = $this->getKategori($id, $params);
            if (isset($kategori['status']) && !$kategori['status']) {
                return $kategori;
            }

            return BaseResponse::success(
                $kategori, 
                'Get detail kategori barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null, 
                'Get detail kategori barang gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }

    /**
     * Proses update kategori
     *
     * @param \App\Http\Requests\BarangKategoriRequest $request
     * @param int                                      $id
     *
     * @return array
     */
    public function update(BarangKategoriRequest $request, $id)
    {
        $input = $request->all();

        try {
            
            $kategori = $this->getKategori($id);
            if (isset($kategori['status']) && !$kategori['status']) {
                return $kategori;
            }

            $this->model->where('id', $id)->update([
                'updated_by' => null,//Auth::user()['id'],
                'nama' => $input['nama'] ?? $kategori['nama'],
                'keterangan' => $input['keterangan'] ?? $kategori['keterangan'],
            ]);
            $kategori = $this->getKategori($id);

            return BaseResponse::success(
                $kategori,
                'Edit kategori barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null, 
                'Edit kategori barang gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }

    /**
     * Proses delete kategori
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        try {
            
            $kategori = $this->getKategori($id);
            if (isset($kategori['status']) && !$kategori['status']) {
                return $kategori;
            }

            $this->model->where('id', $id)->delete();

            return BaseResponse::success(
                [],
                'Delete kategori barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null, 
                'Delete kategori barang gagal. Error : '.$e->getMessage(), 
                $e->getCode()
            );
        }
    }
}
