<?php

namespace App\Http\Controllers;

use App\Models\ChatPesan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /* ---------- Daftar percakapan (admin) ---------- */
    public function index(Request $request)
    {
        if ($request->user()->role === User::ORANG_TUA) return redirect()->route('chat.pribadi');

        $ringkas = ChatPesan::where('jenis', 'pribadi')
            ->selectRaw('orang_tua_id, max(id) as terakhir_id, sum(case when dibaca = 0 and pengirim_id = orang_tua_id then 1 else 0 end) as belum')
            ->groupBy('orang_tua_id')->get()->keyBy('orang_tua_id');

        return view('chat.index', [
            'ortu' => User::where('role', User::ORANG_TUA)->orderBy('name')->get()
                ->sortByDesc(fn ($o) => $ringkas->get($o->id)?->terakhir_id ?? 0)->values(),
            'ringkas' => $ringkas,
            'isiTerakhir' => ChatPesan::whereIn('id', $ringkas->pluck('terakhir_id'))->get()->keyBy('orang_tua_id'),
        ]);
    }

    /* ---------- Chat pribadi orang tua <-> admin ---------- */
    public function pribadi(Request $request)
    {
        $user = $request->user();
        if ($user->role === User::ADMIN && ! $request->filled('ortu')) return redirect()->route('chat.index');

        return view('chat.pribadi', ['ortu' => $this->ortu($request), 'untukAdmin' => $user->role === User::ADMIN]);
    }

    public function pribadiData(Request $request)
    {
        $user = $request->user();
        $ortu = $this->ortu($request);

        $data = $this->ambil(ChatPesan::with('pengirim')->where('jenis', 'pribadi')->where('orang_tua_id', $ortu->id), $request);

        // Tandai pesan dari pihak lawan sebagai sudah dibaca
        $belum = ChatPesan::where('jenis', 'pribadi')->where('orang_tua_id', $ortu->id)->where('dibaca', false);
        if ($user->role === User::ORANG_TUA) {
            $belum->where('pengirim_id', '!=', $ortu->id)->update(['dibaca' => true]);
            $this->tandaiNotif($user, route('chat.pribadi', [], false));
        } else {
            $belum->where('pengirim_id', $ortu->id)->update(['dibaca' => true]);
            $this->tandaiNotif($user, route('chat.pribadi', ['ortu' => $ortu->id], false));
        }

        return response()->json($data->map(fn ($m) => $this->bentuk($m, $user))->values());
    }

    public function pribadiKirim(Request $request)
    {
        $user = $request->user();
        $ortu = $this->ortu($request);
        $request->validate(['isi' => 'required|string|max:2000']);

        $m = ChatPesan::create(['jenis' => 'pribadi', 'orang_tua_id' => $ortu->id, 'pengirim_id' => $user->id, 'isi' => trim($request->isi)]);

        if ($user->role === User::ORANG_TUA) {
            Notifikasi::kirim(User::where('role', User::ADMIN)->pluck('id'), 'chat', 'Pesan dari ' . $user->name,
                Str::limit($m->isi, 80), route('chat.pribadi', ['ortu' => $ortu->id], false));
        } else {
            Notifikasi::kirim([$ortu->id], 'chat', 'Balasan dari admin sekolah',
                Str::limit($m->isi, 80), route('chat.pribadi', [], false));
        }

        return response()->json(['ok' => true]);
    }

    /* ---------- Grup semua orang tua ---------- */
    public function grup()
    {
        return view('chat.grup');
    }

    public function grupData(Request $request)
    {
        $user = $request->user();
        $data = $this->ambil(ChatPesan::with('pengirim')->where('jenis', 'grup'), $request);

        DB::table('chat_grup_baca')->updateOrInsert(['user_id' => $user->id], ['terakhir_id' => (int) ChatPesan::where('jenis', 'grup')->max('id')]);
        $this->tandaiNotif($user, route('chat.grup', [], false));

        return response()->json($data->map(fn ($m) => $this->bentuk($m, $user))->values());
    }

    public function grupKirim(Request $request)
    {
        $user = $request->user();
        $request->validate(['isi' => 'required|string|max:2000']);

        $m = ChatPesan::create(['jenis' => 'grup', 'pengirim_id' => $user->id, 'isi' => trim($request->isi)]);

        Notifikasi::kirim(User::whereIn('role', [User::ORANG_TUA, User::ADMIN])->where('id', '!=', $user->id)->pluck('id'),
            'chat', 'Grup Orang Tua', $user->name . ': ' . Str::limit($m->isi, 80), route('chat.grup', [], false));

        return response()->json(['ok' => true]);
    }

    /* ---------- Pembantu ---------- */
    private function ortu(Request $request): User
    {
        $user = $request->user();
        if ($user->role === User::ORANG_TUA) return $user;

        return User::where('role', User::ORANG_TUA)->findOrFail($request->input('ortu'));
    }

    /** Pesan terbaru (100 terakhir), atau yang lebih baru dari ?setelah=ID. */
    private function ambil($q, Request $request)
    {
        $setelah = (int) $request->query('setelah', 0);

        return $setelah > 0
            ? $q->where('id', '>', $setelah)->orderBy('id')->limit(100)->get()
            : $q->orderByDesc('id')->limit(100)->get()->reverse()->values();
    }

    private function bentuk(ChatPesan $m, User $saya): array
    {
        $t = $m->created_at->copy()->timezone('Asia/Jakarta');

        return [
            'id' => $m->id,
            'mine' => (int) $m->pengirim_id === (int) $saya->id,
            'nama' => $m->pengirim->name ?? 'Pengguna',
            'admin' => ($m->pengirim->role ?? null) === User::ADMIN,
            'isi' => $m->isi,
            'jam' => $t->format('H.i'),
            'tgl' => $t->translatedFormat('l, d F Y'),
            'tgl_key' => $t->format('Y-m-d'),
        ];
    }

    private function tandaiNotif(User $user, string $url): void
    {
        Notifikasi::where('user_id', $user->id)->where('kategori', 'chat')->where('url', $url)->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
    }
}
