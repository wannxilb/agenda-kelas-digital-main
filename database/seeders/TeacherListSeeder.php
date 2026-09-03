<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Scopes\InstitutionScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherListSeeder extends Seeder
{
    public function run()
    {
        $institutionId = 1;
        $teachers = [
            "Ani Lukmayani, S.Pd.,M.Pd",
            "Hj. Eni Widyastuti, S.ST",
            "H. Ruslan Nur Iskandar, S.Pd",
            "Nandang Hermana, S.Pd",
            "Dra. Yanti Kadaryati",
            "Dra. Wiwin Supriatin, M.Pd",
            "Aslim, M.Pd",
            "Sukatmi, S.Pd",
            "Hermin Hindayani, S.Pd., M.Pd.",
            "Isna Nurilah, S.Pd",
            "Kusmawati, S.Pd, M.Pd.",
            "Moh.Abd.Basir Imam B, S.Pd, M",
            "Elis Fitriawati, S.Pd, M.Pd.",
            "Elin Roslianti, S.Pd, M.Pd.",
            "H. Kuswandi, SE",
            "Pusparani, ST",
            "Risdiyan, S.Pd, M.Pd.",
            "Deti Haryati, S.Pd, Kons",
            "Etin Rostika, S.Pd, M.Pd",
            "Lala Maulana, S.Pd.",
            "Neny Yunaeti, M.Pd",
            "Hj. Elis Kusumahwati, S.Pd",
            "Aang Susbijantara Faizar, S.Sos",
            "Asep Indra, S.Kom, MM.",
            "Rini Uswati, S.Pd, M.Pd.",
            "Susi Rukhmiati, S.Pd.",
            "Nurhayati, S.Pd.",
            "Barokatunnafiah, S.Pd.",
            "Iming Lasmini, S.Pd.",
            "Ninik Kurniati, S.Pd.",
            "Deni Suharyanto, S.Pd, M.Pd",
            "Siti Rodiah, S.Pd.",
            "Ikah Atikah, S.Pd., M.Pd.",
            "Sintia, S.Pd., Gr",
            "Dede Rukmana, ST.Gr",
            "Pribadi Ramadhan, S.Pd.",
            "Dewi Fridawati, S.Pd",
            "Rini Dinarwati, S.Pd",
            "Dani Efendi S.Pd.I",
            "Beni Fitrianto Hidayat, S.kom",
            "Imas Rohimah, S.Pd",
            "Lin Karlina Sri Martini S.Pd",
            "Upen, S.Pd",
            "Darsu, S.Kom.",
            "Ari Hendartika, S.Pd.M.Pd",
            "Meytasari, S.Pd",
            "Endang Suhendar, S.Pd. M.Ak.",
            "Rian Fauzi, S.Kom.",
            "Vera Cahya Gumilar, S. Pd., M.M., Gr",
            "Sandi Saputra, S.Kom",
            "H.Ihsan Zaenissalam, S.Pd.M.Pd",
            "Elis Siti Nurjanah, S.Pd.",
            "Vevi Supriyanti, S.Pd",
            "Siti Rohimah, S.Pd",
            "Arief Budiastana Putra, S. Pd",
            "Niawati, S.Pd",
            "Ahmad Muhaimin Rajab, S.Ds",
            "Gugun Gunawan Sapi'i, S.Pd",
            "Siska Rachmawati, S.Pd",
            "Baiman Hadisucipto, SE.",
            "Lina Herlina, S.Pd.",
            "Solihudin, S.Pd.",
            "Roni Susanto, S.Pd.",
            "Fajar Akbar Yanto, S.Pd Jas",
            "Popi Suprapti, S.Pd.",
            "Alpan, M.Pd.",
            "Hany Fitriyanti, S.Pd.",
            "Ibnu Gumilar, S.Pd.",
            "Yusup Zarkasi, ST.",
            "Siti Nurmei Muliati, S.Pd.",
            "Dedeh Kurniasih, S.Pd.",
            "Gun Gun Gurniwa Gautama, S.Pd.",
            "Nasrodin, S.Pd.",
            "Novi Maulani, S.Pd.",
            "Ina Indriyani, S.Pd.",
            "Juliarto, S.Pd.",
            "Putri Marlistiasari, S.Pd.",
            "Alis Nursaleh, S.Pd.",
            "Hj. Iis Tresnawati, S.Pd.",
            "Annisa Kurnia Damayu, M.Pd.",
            "Heni Rosmiati, S.Pd.",
            "Nia Kurniasih, S.E.",
            "Nurul Hidayati, S.T.",
            "Ati Supiati, S.Pd.",
            "Novi Nur Istiqomah, S.Pd.I.",
            "Neng Wida Nurdiani, S.Sos.",
            "Tiara Ristinasari, S.Pd., M.Pd.",
            "Renita Wildy Hernanda, S.Pd.",
        ];

        $i = 1;
        foreach ($teachers as $name) {
            // Bersihkan nama dari gelar untuk email
            $noGelar = preg_replace('/[,.]?\s*(S\.?\s*Pd(?:\.I)?\.?|M\.?\s*Pd\.?|S\.?\s*ST\.?|Dra\.?|M\.?\s*M\.?|S\.?\s*Sos\.?|S\.?\s*Kom\.?|M\.?\s*Ak\.?|S\.?\s*Ds\.?|S\.?\s*E\.?|S\.?\s*T\.?|MM\.?|Jas|Gr\.?|Kons\.?)\b/i', '', $name);
            $noGelar = preg_replace('/\b(H\.|Hj\.)\s*/i', '', $noGelar);
            $cleanName = trim(preg_replace('/[,\s.]+/', ' ', $noGelar));

            $slug = Str::slug($cleanName);
            $email = $slug . '@school.com';

            // Handle duplicate emails dalam institusi yang sama
            $counter = 1;
            while (User::withoutGlobalScope(InstitutionScope::class)->where('email', $email)->where('institution_id', $institutionId)->exists()) {
                $email = $slug . $counter . '@school.com';
                $counter++;
            }

            // Cek apakah guru dengan nama ini sudah ada di institusi yang sama
            $existing = User::withoutGlobalScope(InstitutionScope::class)
                ->where('institution_id', $institutionId)
                ->whereRaw("TRIM(name) = ?", [trim($name)])
                ->first();
            if ($existing) {
                if (!$existing->hasRole('teacher')) {
                    $existing->assignRole('teacher');
                }
                $i++;
                continue;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'institution_id' => $institutionId,
            ]);

            $user->assignRole('teacher');
            $i++;
        }
    }
}
