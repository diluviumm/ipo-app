<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Support\Audit;
use App\Support\PasswordKuat;

class ProfilController extends Controller
{
    public function password(Request $request)
    {
        return view('profil.password', ['me' => DB::table('users')->where('id', $request->user()->id)->first()]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate(
            ['lama' => 'required', 'baru' => 'required|min:6|confirmed'],
            [
                'lama.required' => 'Password lama wajib diisi',
                'baru.required' => 'Password baru wajib diisi',
                'baru.min' => 'Password baru minimal 6 karakter',
                'baru.confirmed' => 'Konfirmasi password baru tidak sama',
            ]
        );
        $u = $request->user();
        if (!Hash::check($data['lama'], $u->password_hash)) {
            return back()->withErrors(['lama' => __('Password lama salah')])->withInput();
        }
        if ($tolak = PasswordKuat::cek($data['baru'])) {
            return back()->withErrors(['baru' => $tolak])->withInput();
        }
        DB::table('users')->where('id', $u->id)->update(['password_hash' => Hash::make($data['baru'])]);
        return redirect('/dashboard')->with('toast', ['type' => 'success', 'text' => __('Password berhasil diganti.')]);
    }

    public function mulai2fa(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403, 'Hanya admin & operator');
        $g = new \PragmaRX\Google2FA\Google2FA;
        $secret = $g->generateSecretKey();
        $request->session()->put('calon_2fa', $secret);
        $qr = (new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(220),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        ))->writeString($g->getQRCodeUrl('IPO', $request->user()->username, $secret));
        return view('profil.aktif2fa', ['secret' => $secret, 'qr' => $qr]);
    }

    public function simpan2fa(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403, 'Hanya admin & operator');
        $secret = $request->session()->get('calon_2fa');
        abort_unless($secret, 400, 'Sesi aktivasi kedaluwarsa, ulangi dari awal');
        $kode = preg_replace('/\D/', '', (string) $request->input('kode', ''));
        if (!(new \PragmaRX\Google2FA\Google2FA)->verifyKey($secret, $kode)) {
            return back()->withErrors(['kode' => __('Kode salah, pindai ulang lalu coba lagi')]);
        }
        DB::table('users')->where('id', $request->user()->id)->update(['totp_secret' => $secret, 'totp_aktif' => true]);
        $request->session()->forget('calon_2fa');
        Audit::catat($request->user(), 'ubah', 'users', $request->user()->id, ['2fa' => 'aktif']);
        return redirect('/profil/password')->with('toast', ['type' => 'success', 'text' => __('Verifikasi 2 langkah aktif.')]);
    }

    public function mati2fa(Request $request)
    {
        $data = $request->validate(['lama' => 'required']);
        $u = $request->user();
        if (!Hash::check($data['lama'], $u->password_hash)) {
            return back()->withErrors(['lama' => __('Password salah')])->withInput();
        }
        DB::table('users')->where('id', $u->id)->update(['totp_secret' => null, 'totp_aktif' => false]);
        return redirect('/profil/password')->with('toast', ['type' => 'success', 'text' => __('Verifikasi 2 langkah dimatikan.')]);
    }
}
