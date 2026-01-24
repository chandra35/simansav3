<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('urutan')->get();
        return view('admin.ppdb.slider.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.ppdb.slider.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'link' => 'nullable|url',
            'urutan' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->except('gambar');

        // Upload gambar
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('sliders', $filename, 'public');
            $data['gambar'] = $path;
        }

        Slider::create($data);

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil ditambahkan.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.ppdb.slider.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'link' => 'nullable|url',
            'urutan' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->except('gambar');

        // Upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($slider->gambar && Storage::disk('public')->exists($slider->gambar)) {
                Storage::disk('public')->delete($slider->gambar);
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('sliders', $filename, 'public');
            $data['gambar'] = $path;
        }

        $slider->update($data);

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil diupdate.');
    }

    public function destroy(Slider $slider)
    {
        // Hapus gambar
        if ($slider->gambar && Storage::disk('public')->exists($slider->gambar)) {
            Storage::disk('public')->delete($slider->gambar);
        }

        $slider->delete();

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil dihapus.');
    }

    public function toggleStatus(Slider $slider)
    {
        $slider->status = $slider->status === 'active' ? 'inactive' : 'active';
        $slider->save();

        return response()->json([
            'success' => true,
            'status' => $slider->status,
            'message' => 'Status slider berhasil diubah.'
        ]);
    }
}
