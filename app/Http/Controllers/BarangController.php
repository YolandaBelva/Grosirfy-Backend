<?php

namespace App\Http\Controllers;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Base\BaseResponse;
use App\Http\Requests\BarangRequest;

use App\Models\Barang;

class BarangController extends Controller
{
    private $model;

    public function __construct(Barang $model)
    {
        $this->model = $model;
    }

    /**
     * List barang
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function list(Request $request)
{
    // Initialize query
    $query = $this->model->query();

    // Load 'kategori' relation if necessary
    $query->with(['kategori' => function ($query) {
        $query->select('id', 'nama');
    }]);

    // Handle additional with-relations dynamically
    if ($request->input('with')) {
        $query->with($request->input('with'));
    }

    // Apply filters if provided
    $query->when($request->input('kategori_id'), function ($queryB, $kategoriId) {
        return $queryB->where('kategori_id', $kategoriId);
    });

    $query->when($request->input('q'), function ($query, $q) {
        return $query->where('kode', 'LIKE', "%$q%")
                     ->orWhere('nama', 'LIKE', "%$q%");
    });

    // Execute query
    $rawResult = $query->get();

    // Map the data conditionally
    $mappedResult = $rawResult->map(function ($item) {
        return [
            'id' => $item->id,
            'kode' => $item->kode,
            'nama' => $item->nama,
            'kategori_id' => $item->kategori_id,
            'nama_kategori' => optional($item->kategori)->nama ?? 'No Kategori' // Dynamically fetch name
        ];
    });

    // Convert to array
    // $resultArray = $mappedResult->toArray();

    // Show raw `$result`
    $result = $rawResult->toArray();

    return BaseResponse::success(
        [
            // 'mappedResult' => $resultArray,
            'barang' => $result
        ],
        'Get list barang berhasil'
    );
}




    /**
     * Get data barang
     *
     * @param int   $id
     * @param array $params
     *
     * @return mixed
     */
    private function getBarang($id, $params = [])
    {
        $barang = $this->model->query();
        if (isset($params['with'])) {
            $barang->with($params['with']);
        }
        $barang = $barang->firstWhere(['id' => $id]);

        if (!$barang) {
            return BaseResponse::error(
                $barang,
                'Get data barang gagal',
                [],
                404
            );
        }

        return $barang;
    }

    /**
     * Proses create barang
     *
     * @param \App\Http\Requests\BarangRequest $request
     *
     * @return array
     */
    public function store(BarangRequest $request)
    {
        $input = $request->all();

        try {

            $create = $this->model->create([
                'created_by' => null,//Auth::user()['id'],
                'kategori_id' => $input['kategori_id'],
                'kode' => $input['kode'],
                'nama' => $input['nama'],
                'satuan' => $input['satuan'],
                'harga' => $input['harga']
            ]);

            return BaseResponse::success(
                $create,
                'Tambah barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null,
                'Tambah barang gagal. Error : '.$e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
    * Get detail barang
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

            $barang = $this->getBarang($id, $params);
            if (isset($barang['status']) && !$barang['status']) {
                return $barang;
            }

            return BaseResponse::success(
                $barang,
                'Get detail barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null,
                'Get detail barang gagal. Error : '.$e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Proses update barang
     *
     * @param \App\Http\Requests\BarangRequest $request
     * @param int                              $id
     *
     * @return array
     */
    public function update(BarangRequest $request, $id)
    {
        $input = $request->all();

        try {

            $barang = $this->getBarang($id);
            if (isset($barang['status']) && !$barang['status']) {
                return $barang;
            }

            $this->model->where('id', $id)->update([
                'updated_by' => null,//Auth::user()['id'],
                'kategori_id' => $input['kategori_id'],
                'kode' => $input['kode'] ?? $barang['kode'],
                'nama' => $input['nama'] ?? $barang['nama'],
                'satuan' => $input['satuan'] ?? $barang['satuan'],
                'harga' => $input['harga'] ?? $barang['harga']
            ]);
            $barang = $this->getBarang($id);

            return BaseResponse::success(
                $barang,
                'Edit barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null,
                'Edit barang gagal. Error : '.$e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Proses delete barang
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        try {

            $barang = $this->getBarang($id);
            if (isset($barang['status']) && !$barang['status']) {
                return $barang;
            }

            $this->model->where('id', $id)->delete();

            return BaseResponse::success(
                [],
                'Delete barang berhasil'
            );

        } catch (Exception $e) {
            return BaseResponse::error(
                null,
                'Delete barang gagal. Error : '.$e->getMessage(),
                $e->getCode()
            );
        }
    }
}
