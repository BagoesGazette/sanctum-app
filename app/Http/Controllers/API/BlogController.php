<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Traits\ReturnResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    use ReturnResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page   = $request->input('per_page');
        $search     = $request->input('search');

        $lists      = $per_page == NULL ? Blog::searchable($search)->latest('id')->get() : Blog::searchable($search)->latest('id')->paginate($per_page);

        return $this->success($lists, 'Data blog berhasil ditampilkan');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(),[
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'required|mimes:png,jpg,webp'
        ]);

        if ($validasi->fails()) {
            return $this->failed(null, $validasi->errors());
        } else {
            $image = $request->file('image');

            $image->storeAs('public/blogs', $image->hashName());
            $result = Blog::create([
                'uuid'          => Str::uuid(),
                'title'         => $request->input('title'),
                'description'   => $request->input('description'),
                'image'         => $image->hashName()
            ]);

            if ($result) {
                return $this->success($result, 'Data blog berhasil ditambahkan');
            }
            return $this->failed($request->all(), 'Data blog gagal ditambahkan');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $detail = Blog::find($id);

        if ($detail) {
            return $this->success($detail, 'Data blog berhasil ditemukan');
        }
        return $this->failed($detail, 'Data blog tidak ditemukan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(),[
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'mimes:png,jpg,webp'
        ]);

        if ($validasi->fails()) {
            return $this->failed(null, $validasi->errors());
        } else {
            $detail = Blog::find($id); 
            $image  = $request->file('image');
            
            if ($image) {
                Storage::disk('local')->delete('public/blogs/'.basename($detail->image));
                $image->storeAs('public/blogs', $image->hashName());
                $resultImage = $image->hashName();
            }else{
                $resultImage = basename($detail->image);
            }
            
            $detail->update([
                'title'         => $request->input('title'),
                'description'   => $request->input('description'),
                'image'         => $resultImage
            ]);

            if ($detail) {
                return $this->success($detail, 'Data blog berhasil diupdate');
            }
            return $this->failed($request->all(), 'Data blog gagal diupdate');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $detail = Blog::find($id);

        if ($detail) {
            if ($detail->image) {
                Storage::disk('local')->delete('public/blogs/'.basename($detail->image));
            }
            $detail->delete();

            return $this->success($detail, 'Data blog berhasil dihapus');
        }
        return $this->failed($detail, 'Data blog tidak ditemukan');
    }
}
