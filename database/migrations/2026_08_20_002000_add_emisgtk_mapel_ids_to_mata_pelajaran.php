<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->json('emisgtk_mapel_ids')->nullable()->after('kode_jadwal');
        });

        $references = [
            [10, 'B', '4c6adfff68104f2789fa3f'], [10, 'A', '65366fb7e2554fb8a85c95'],
            [10, 'E', '27da6cfab8424ebc8816f9'], [10, 'G', '0e257fb0ca8e4096baf2cb'],
            [10, 'H', '0f1a1a7756c44c4f8ec462'], [10, 'M', 'a50087eed36c4eb88815b1'],
            [10, 'Q', 'ee039dc990cc49ecb09ace'], [10, 'C', '9e8fd0d3eb424143810dda'],
            [10, 'K', 'd375767d4de54a1b8babf5'], [10, 'P', '204d76b3026c4a95a241e2'],
            [10, 'Y', '4505a7bad12e4ad98bc340'], [10, 'L', '920381d3a31a48c486e91f'],
            [10, 'I', '83f0d965e5054973a2f491'], [10, 'W', '79ad825d74574aa3a7b8a5'],
            [10, 'X', '65ddda888dad4f23b8a9a2'], [10, 'S', '6c4e4e9111bc4366bbb404'],
            [10, 'F', 'eea90399654f42088e726f'], [10, 'V', '5522b137ed454f5cae0dda'],
            [10, 'O', '7fad0bc3a741444181be0a'], [10, 'D', 'f4d8a3bb193a4e02851db8'],
            [10, 'T', 'c78a31b9f9de4567a47b86'], [10, 'R', '48011c1d69a34af59df371'],
            [10, 'U', 'ecbcfb08fdfb4379942b08'],
            [11, 'B', 'b962646eda6140c38ef66a'], [11, 'A', 'fddb66de45994c5b8f2ba0'],
            [11, 'E', '55d1a730ec024c5dac148d'], [11, 'G', '0453c2bc3056451d91e047'],
            [11, 'H', '236c87bde98f498895c0a3'], [11, 'Z', 'fa3b4ce5fe544a8f8e43de'],
            [11, 'M', '129cd2a607774c6bbd6d1f'], [11, 'Q', 'd2494316131a4cdc864ab8'],
            [11, 'C', 'c360a39a5830473793d220'], [11, 'K', '5a39cc7c4586412da8da59'],
            [11, 'P', '59eca221ebcb42d7bea234'], [11, 'Y', 'e0df3df085524a82b49093'],
            [11, 'L', 'f8a5611cb5c544b9b250b1'], [11, 'I', '2ef5a602c3ab450595d935'],
            [11, 'J', '5997cab83ef0426a8ed406'], [11, 'W', '5514ab748e8141e1ba31d1'],
            [11, 'X', 'f4a7f5d37ef84bbc8d8d21'], [11, 'S', '2362f05332ee48fc939bab'],
            [11, 'F', '12dffd35b88e4d81b35de0'], [11, 'V', 'b9c052a836d64d48af986b'],
            [11, 'O', '52d7dfd494af445c8e7ca2'], [11, 'D', '0850386c44944bcd95b3ec'],
            [11, 'N', '10e17a50061e4bd6b44af2'], [11, 'T', '10b2221e0d664f839048eb'],
            [11, 'R', '710c759e298746a589f019'], [11, 'U', 'eb9c8d3ed2f648ff836651'],
            // Sheet referensi menyediakan dua entri kode Y tingkat 11. Entri
            // TIK di bawah adalah yang dipakai template jadwal XI/XII aktif.
            [11, 'Y', 'ddd20690bf864fba9bde50'],
            [12, 'B', '5da4aa1274be49069901bd'], [12, 'A', 'ad835f2c98134d25ad5519'],
            [12, 'E', '2381e98b41894d3c951268'], [12, 'G', 'f74d1d3ac3f343db8b44d0'],
            [12, 'H', 'cb2af621b56b4442b66f7c'], [12, 'Z', 'b7801b4f833a49f6b4109e'],
            [12, 'M', '13252ebecf87463ab2d8d4'], [12, 'Q', 'f7320d93cbc94981af0217'],
            [12, 'C', 'e0129e54ff1c4399a0ae45'], [12, 'K', '9b35cdb6d4fb4251a691d5'],
            [12, 'P', '1f719df578c34ce4a5872f'], [12, 'Y', '186f33c2d4774fc7b1f3d8'],
            [12, 'L', '651ee1e1e255435abea194'], [12, 'I', '7346e808ad1b4b159ae267'],
            [12, 'J', '753f3b6b51774a0e97c11a'], [12, 'W', 'b3b6524ecd94489898d20a'],
            [12, 'X', '754adb6755ab4a15a64556'], [12, 'S', 'e1a39a4281df4b60bc5ca9'],
            [12, 'F', 'b0d9dad01677433d8d96ed'], [12, 'O', '3bdd92ce942e4aa2bb9c5b'],
            [12, 'D', 'e1d2c1f4a84945da992767'], [12, 'N', '0f729cd59c804a2a91228d'],
            [12, 'T', '483adb63aaba4ef9a811fa'], [12, 'R', '37b9a18b051d48f8bc9939'],
            [12, 'U', 'a51da47694834c798b3f9a'],
        ];

        $mapByLevelAndCode = [];
        foreach ($references as [$tingkat, $kode, $emisId]) {
            $mapByLevelAndCode[$kode][(string) $tingkat] = $emisId;
        }

        $merdekaId = DB::table('kurikulum')->where('kode', 'MERDEKA')->value('id');
        if (! $merdekaId) {
            return;
        }

        DB::table('mata_pelajaran')
            ->where('kurikulum_id', $merdekaId)
            ->whereNotNull('kode_jadwal')
            ->get(['id', 'kode_jadwal'])
            ->each(function (object $mapel) use ($mapByLevelAndCode): void {
                $ids = $mapByLevelAndCode[$mapel->kode_jadwal] ?? null;
                if ($ids) {
                    DB::table('mata_pelajaran')->where('id', $mapel->id)->update([
                        'emisgtk_mapel_ids' => json_encode($ids),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn('emisgtk_mapel_ids');
        });
    }
};
