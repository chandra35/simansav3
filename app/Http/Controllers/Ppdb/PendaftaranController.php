<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\DokumenPendaftaran;
use App\Models\JurusanPpdb;
use App\Models\PendaftaranPpdb;
use App\Models\PengaturanPpdb;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PendaftaranController extends Controller
{
    /**
     * Display the registration start page
     */
    public function index()
    {
        $pengaturan = PengaturanPpdb::getActive();
        
        if (!$pengaturan->isPendaftaranDibuka()) {
            return view('ppdb.pendaftaran.closed', compact('pengaturan'));
        }

        $jurusan = JurusanPpdb::active()->ordered()->get();
        
        return view('ppdb.pendaftaran.index', compact('pengaturan', 'jurusan'));
    }

    /**
     * Step 1: Validate NISN
     */
    public function step1()
    {
        $pengaturan = PengaturanPpdb::getActive();
        
        if (!$pengaturan->isPendaftaranDibuka()) {
            return redirect()->route('ppdb.pendaftaran.index');
        }

        return view('ppdb.pendaftaran.step1-nisn', compact('pengaturan'));
    }

    /**
     * Process Step 1: Validate NISN and create draft
     */
    public function processStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|string|size:10|regex:/^[0-9]+$/',
            'nama_lengkap' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
        ], [
            'nisn.required' => 'NISN wajib diisi',
            'nisn.size' => 'NISN harus 10 digit',
            'nisn.regex' => 'NISN hanya boleh berisi angka',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if NISN already registered this year
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $existingPendaftaran = PendaftaranPpdb::where('nisn', $request->nisn)
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereNotIn('status', [PendaftaranPpdb::STATUS_REJECTED])
            ->first();

        if ($existingPendaftaran) {
            if ($existingPendaftaran->status === PendaftaranPpdb::STATUS_DRAFT) {
                // Continue existing draft
                session(['pendaftaran_token' => $existingPendaftaran->token]);
                return redirect()->route('ppdb.pendaftaran.step2');
            }
            
            return back()->withErrors(['nisn' => 'NISN sudah terdaftar pada periode ini. Silakan cek status pendaftaran Anda.'])->withInput();
        }

        // Validate NISN with Kemendikbud API (optional)
        $nisnData = $this->validateNisnKemendikbud($request->nisn, $request->nama_lengkap, $request->tanggal_lahir);

        DB::beginTransaction();
        try {
            // Create new pendaftaran
            $pendaftaran = PendaftaranPpdb::create([
                'nomor_pendaftaran' => PendaftaranPpdb::generateNomorPendaftaran($tahunAktif?->id),
                'tahun_pelajaran_id' => $tahunAktif?->id,
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'tanggal_lahir' => $request->tanggal_lahir,
                'token' => PendaftaranPpdb::generateToken(),
                'step_terakhir' => PendaftaranPpdb::STEP_NISN,
                'status' => PendaftaranPpdb::STATUS_DRAFT,
                'data_sementara' => $nisnData,
            ]);

            session(['pendaftaran_token' => $pendaftaran->token]);
            
            DB::commit();
            
            return redirect()->route('ppdb.pendaftaran.step2')
                ->with('success', 'Data NISN berhasil divalidasi. Silakan lengkapi data pribadi.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pendaftaran: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Step 2: Personal Data Form
     */
    public function step2()
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return redirect()->route('ppdb.pendaftaran.step1')
                ->with('error', 'Silakan mulai dari awal.');
        }

        $pengaturan = PengaturanPpdb::getActive();
        $jurusan = JurusanPpdb::active()->ordered()->get();
        
        return view('ppdb.pendaftaran.step2-data-pribadi', compact('pendaftaran', 'pengaturan', 'jurusan'));
    }

    /**
     * Process Step 2: Save Personal Data
     */
    public function processStep2(Request $request)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return redirect()->route('ppdb.pendaftaran.step1');
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|regex:/^[0-9]+$/',
            'tempat_lahir' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string|max:20',
            'alamat' => 'required|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'kelurahan' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'npsn_asal_sekolah' => 'nullable|string|max:20',
            'alamat_asal_sekolah' => 'nullable|string',
            'tahun_lulus' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'jurusan_pilihan_1' => 'required|exists:jurusan_ppdb,id',
            'jurusan_pilihan_2' => 'nullable|exists:jurusan_ppdb,id|different:jurusan_pilihan_1',
            'jalur_pendaftaran' => 'required|in:reguler,prestasi,afirmasi,zonasi',
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'nik.regex' => 'NIK hanya boleh berisi angka',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'agama.required' => 'Agama wajib dipilih',
            'alamat.required' => 'Alamat wajib diisi',
            'kelurahan.required' => 'Kelurahan wajib diisi',
            'kecamatan.required' => 'Kecamatan wajib diisi',
            'kabupaten.required' => 'Kabupaten wajib diisi',
            'provinsi.required' => 'Provinsi wajib diisi',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'asal_sekolah.required' => 'Asal sekolah wajib diisi',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi',
            'jurusan_pilihan_1.required' => 'Pilihan jurusan 1 wajib dipilih',
            'jalur_pendaftaran.required' => 'Jalur pendaftaran wajib dipilih',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $pendaftaran->update([
                'nik' => $request->nik,
                'tempat_lahir' => $request->tempat_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
                'kabupaten' => $request->kabupaten,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'asal_sekolah' => $request->asal_sekolah,
                'npsn_asal_sekolah' => $request->npsn_asal_sekolah,
                'alamat_asal_sekolah' => $request->alamat_asal_sekolah,
                'tahun_lulus' => $request->tahun_lulus,
                'jurusan_pilihan_1' => $request->jurusan_pilihan_1,
                'jurusan_pilihan_2' => $request->jurusan_pilihan_2,
                'jalur_pendaftaran' => $request->jalur_pendaftaran,
                'step_terakhir' => max($pendaftaran->step_terakhir, PendaftaranPpdb::STEP_DATA_PRIBADI),
            ]);

            return redirect()->route('ppdb.pendaftaran.step3')
                ->with('success', 'Data pribadi berhasil disimpan.');
                
        } catch (\Exception $e) {
            Log::error('Error saving step 2: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.'])->withInput();
        }
    }

    /**
     * Step 3: Parent Data Form
     */
    public function step3()
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran || $pendaftaran->step_terakhir < PendaftaranPpdb::STEP_NISN) {
            return redirect()->route('ppdb.pendaftaran.step1');
        }

        $pengaturan = PengaturanPpdb::getActive();
        
        return view('ppdb.pendaftaran.step3-data-orangtua', compact('pendaftaran', 'pengaturan'));
    }

    /**
     * Process Step 3: Save Parent Data
     */
    public function processStep3(Request $request)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return redirect()->route('ppdb.pendaftaran.step1');
        }

        $validator = Validator::make($request->all(), [
            'nama_ayah' => 'required|string|max:255',
            'nik_ayah' => 'nullable|string|size:16|regex:/^[0-9]+$/',
            'pekerjaan_ayah' => 'required|string|max:100',
            'penghasilan_ayah' => 'required|string|max:50',
            'no_hp_ayah' => 'nullable|string|max:20',
            'nama_ibu' => 'required|string|max:255',
            'nik_ibu' => 'nullable|string|size:16|regex:/^[0-9]+$/',
            'pekerjaan_ibu' => 'required|string|max:100',
            'penghasilan_ibu' => 'required|string|max:50',
            'no_hp_ibu' => 'nullable|string|max:20',
            'nama_wali' => 'nullable|string|max:255',
            'nik_wali' => 'nullable|string|size:16|regex:/^[0-9]+$/',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'penghasilan_wali' => 'nullable|string|max:50',
            'no_hp_wali' => 'nullable|string|max:20',
            'hubungan_wali' => 'nullable|string|max:50',
            'alamat_orangtua' => 'required|string',
        ], [
            'nama_ayah.required' => 'Nama ayah wajib diisi',
            'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib dipilih',
            'penghasilan_ayah.required' => 'Penghasilan ayah wajib dipilih',
            'nama_ibu.required' => 'Nama ibu wajib diisi',
            'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib dipilih',
            'penghasilan_ibu.required' => 'Penghasilan ibu wajib dipilih',
            'alamat_orangtua.required' => 'Alamat orang tua wajib diisi',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $pendaftaran->update([
                'nama_ayah' => $request->nama_ayah,
                'nik_ayah' => $request->nik_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'penghasilan_ayah' => $request->penghasilan_ayah,
                'no_hp_ayah' => $request->no_hp_ayah,
                'nama_ibu' => $request->nama_ibu,
                'nik_ibu' => $request->nik_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'penghasilan_ibu' => $request->penghasilan_ibu,
                'no_hp_ibu' => $request->no_hp_ibu,
                'nama_wali' => $request->nama_wali,
                'nik_wali' => $request->nik_wali,
                'pekerjaan_wali' => $request->pekerjaan_wali,
                'penghasilan_wali' => $request->penghasilan_wali,
                'no_hp_wali' => $request->no_hp_wali,
                'hubungan_wali' => $request->hubungan_wali,
                'alamat_orangtua' => $request->alamat_orangtua,
                'step_terakhir' => max($pendaftaran->step_terakhir, PendaftaranPpdb::STEP_DATA_ORANGTUA),
            ]);

            return redirect()->route('ppdb.pendaftaran.step4')
                ->with('success', 'Data orang tua berhasil disimpan.');
                
        } catch (\Exception $e) {
            Log::error('Error saving step 3: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.'])->withInput();
        }
    }

    /**
     * Step 4: Upload Documents
     */
    public function step4()
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran || $pendaftaran->step_terakhir < PendaftaranPpdb::STEP_DATA_PRIBADI) {
            return redirect()->route('ppdb.pendaftaran.step2');
        }

        $pengaturan = PengaturanPpdb::getActive();
        $jenisDokumen = DokumenPendaftaran::getJenisDokumenList();
        $uploadedDokumen = $pendaftaran->dokumen->keyBy('jenis_dokumen');
        
        return view('ppdb.pendaftaran.step4-upload-dokumen', compact('pendaftaran', 'pengaturan', 'jenisDokumen', 'uploadedDokumen'));
    }

    /**
     * Process Step 4: Upload single document via AJAX
     */
    public function uploadDokumen(Request $request)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $validator = Validator::make($request->all(), [
            'jenis_dokumen' => 'required|string|max:50',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $file = $request->file('file');
            $jenis = $request->jenis_dokumen;
            
            // Delete existing file for this jenis
            $existing = $pendaftaran->dokumen()->where('jenis_dokumen', $jenis)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->path_file);
                $existing->delete();
            }

            // Store new file
            $path = $file->store('ppdb/dokumen/' . $pendaftaran->id, 'public');
            
            $dokumen = DokumenPendaftaran::create([
                'pendaftaran_id' => $pendaftaran->id,
                'jenis_dokumen' => $jenis,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'ukuran_file' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status_verifikasi' => DokumenPendaftaran::STATUS_PENDING,
            ]);

            return response()->json([
                'success' => true,
                'dokumen' => [
                    'id' => $dokumen->id,
                    'nama_file' => $dokumen->nama_file,
                    'url' => $dokumen->file_url,
                    'size' => $dokumen->formatted_size,
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error uploading document: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengupload file'], 500);
        }
    }

    /**
     * Delete uploaded document via AJAX
     */
    public function deleteDokumen(Request $request, $id)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $dokumen = $pendaftaran->dokumen()->find($id);
        if (!$dokumen) {
            return response()->json(['error' => 'Dokumen tidak ditemukan'], 404);
        }

        try {
            Storage::disk('public')->delete($dokumen->path_file);
            $dokumen->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus file'], 500);
        }
    }

    /**
     * Process Step 4: Complete upload step
     */
    public function processStep4(Request $request)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return redirect()->route('ppdb.pendaftaran.step1');
        }

        // Check mandatory documents
        $wajibDokumen = DokumenPendaftaran::getDokumenWajib();
        $uploadedTypes = $pendaftaran->dokumen->pluck('jenis_dokumen')->toArray();
        
        $missing = [];
        foreach ($wajibDokumen as $jenis => $info) {
            if (!in_array($jenis, $uploadedTypes)) {
                $missing[] = $info['nama'];
            }
        }

        if (!empty($missing)) {
            return back()->withErrors(['dokumen' => 'Dokumen wajib belum lengkap: ' . implode(', ', $missing)]);
        }

        $pendaftaran->update([
            'step_terakhir' => max($pendaftaran->step_terakhir, PendaftaranPpdb::STEP_UPLOAD_DOKUMEN),
        ]);

        return redirect()->route('ppdb.pendaftaran.step5')
            ->with('success', 'Dokumen berhasil disimpan.');
    }

    /**
     * Step 5: Review and Submit
     */
    public function step5()
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran || $pendaftaran->step_terakhir < PendaftaranPpdb::STEP_DATA_ORANGTUA) {
            return redirect()->route('ppdb.pendaftaran.step3');
        }

        $pendaftaran->load('dokumen');
        $pengaturan = PengaturanPpdb::getActive();
        $jurusanPilihan1 = JurusanPpdb::find($pendaftaran->jurusan_pilihan_1);
        $jurusanPilihan2 = JurusanPpdb::find($pendaftaran->jurusan_pilihan_2);
        $jenisDokumen = DokumenPendaftaran::getJenisDokumenList();
        
        return view('ppdb.pendaftaran.step5-review', compact(
            'pendaftaran', 
            'pengaturan', 
            'jurusanPilihan1', 
            'jurusanPilihan2',
            'jenisDokumen'
        ));
    }

    /**
     * Process Step 5: Submit registration
     */
    public function submit(Request $request)
    {
        $pendaftaran = $this->getPendaftaran();
        if (!$pendaftaran) {
            return redirect()->route('ppdb.pendaftaran.step1');
        }

        if (!$pendaftaran->canBeSubmitted()) {
            return back()->withErrors(['error' => 'Pendaftaran tidak dapat dikirim. Pastikan semua data sudah lengkap.']);
        }

        // Check mandatory documents again
        $wajibDokumen = DokumenPendaftaran::getDokumenWajib();
        $uploadedTypes = $pendaftaran->dokumen->pluck('jenis_dokumen')->toArray();
        
        foreach ($wajibDokumen as $jenis => $info) {
            if (!in_array($jenis, $uploadedTypes)) {
                return back()->withErrors(['error' => 'Dokumen wajib belum lengkap.']);
            }
        }

        DB::beginTransaction();
        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_SUBMITTED,
                'step_terakhir' => PendaftaranPpdb::STEP_REVIEW,
            ]);

            DB::commit();

            // Clear session
            session()->forget('pendaftaran_token');

            return redirect()->route('ppdb.pendaftaran.success', ['token' => $pendaftaran->token])
                ->with('success', 'Pendaftaran berhasil dikirim!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting registration: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Success page after submission
     */
    public function success($token)
    {
        $pendaftaran = PendaftaranPpdb::where('token', $token)->firstOrFail();
        
        return view('ppdb.pendaftaran.success', compact('pendaftaran'));
    }

    /**
     * Check registration status
     */
    public function cekStatus()
    {
        return view('ppdb.pendaftaran.cek-status');
    }

    /**
     * Process status check
     */
    public function processCekStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_pendaftaran' => 'required_without:nisn',
            'nisn' => 'required_without:nomor_pendaftaran',
            'tanggal_lahir' => 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $query = PendaftaranPpdb::query();
        
        if ($request->filled('nomor_pendaftaran')) {
            $query->where('nomor_pendaftaran', $request->nomor_pendaftaran);
        } else {
            $query->where('nisn', $request->nisn);
        }
        
        $pendaftaran = $query->whereDate('tanggal_lahir', $request->tanggal_lahir)->first();

        if (!$pendaftaran) {
            return back()->withErrors(['error' => 'Data pendaftaran tidak ditemukan.'])->withInput();
        }

        return view('ppdb.pendaftaran.status', compact('pendaftaran'));
    }

    /**
     * Continue draft registration
     */
    public function continueDraft($token)
    {
        $pendaftaran = PendaftaranPpdb::where('token', $token)
            ->where('status', PendaftaranPpdb::STATUS_DRAFT)
            ->firstOrFail();

        session(['pendaftaran_token' => $token]);

        // Redirect to appropriate step
        $step = $pendaftaran->step_terakhir + 1;
        $step = min($step, PendaftaranPpdb::STEP_REVIEW);
        
        return redirect()->route('ppdb.pendaftaran.step' . $step);
    }

    /**
     * Get current pendaftaran from session
     */
    private function getPendaftaran(): ?PendaftaranPpdb
    {
        $token = session('pendaftaran_token');
        if (!$token) {
            return null;
        }

        return PendaftaranPpdb::where('token', $token)
            ->whereIn('status', [PendaftaranPpdb::STATUS_DRAFT, PendaftaranPpdb::STATUS_REJECTED])
            ->first();
    }

    /**
     * Validate NISN with Kemendikbud API
     */
    private function validateNisnKemendikbud(string $nisn, string $nama, string $tanggalLahir): ?array
    {
        try {
            // This is a placeholder - implement actual API call if available
            // The Kemendikbud API requires special credentials
            
            return [
                'nisn' => $nisn,
                'nama' => $nama,
                'tanggal_lahir' => $tanggalLahir,
                'validated' => false,
                'message' => 'API validation not available',
            ];
        } catch (\Exception $e) {
            Log::warning('NISN validation failed: ' . $e->getMessage());
            return null;
        }
    }
}
