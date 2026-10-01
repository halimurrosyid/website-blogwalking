<?php

namespace App\Services;

class TldCatalogService
{
    /**
     * Comprehensive catalog of Top-Level Domains (TLDs) worldwide,
     * categorized for intuitive search and selection by Super Admin.
     *
     * @return array<string, array<string, string>>
     */
    public static function groupedCatalog(): array
    {
        return [
            '🇮🇩 Indonesia (.id & Subdomain)' => [
                '.id' => '.id — ccTLD Resmi Republik Indonesia',
                '.co.id' => '.co.id — Perusahaan & Bisnis Indonesia',
                '.web.id' => '.web.id — Website Pribadi, Blog & Komunitas',
                '.my.id' => '.my.id — Personal Blog & Identitas Diri',
                '.biz.id' => '.biz.id — UMKM, Bisnis Mikro & Startup',
                '.or.id' => '.or.id — Organisasi, Lembaga & Yayasan',
                '.ac.id' => '.ac.id — Perguruan Tinggi, Institut & Universitas',
                '.sch.id' => '.sch.id — Sekolah Indonesia (SD/SMP/SMA/SMK)',
                '.go.id' => '.go.id — Instansi Pemerintah Republik Indonesia',
                '.desa.id' => '.desa.id — Website Resmi Pemerintahan Desa',
                '.ponpes.id' => '.ponpes.id — Pondok Pesantren Indonesia',
                '.net.id' => '.net.id — Penyelenggara Jasa Telekomunikasi',
                '.mil.id' => '.mil.id — Militer / TNI Indonesia',
            ],

            '🌐 Global & Komersial Populer' => [
                '.com' => '.com — Komersial Global (Paling Populer di Dunia)',
                '.net' => '.net — Jaringan, Internet & Infrastruktur',
                '.org' => '.org — Organisasi & Entitas Global',
                '.info' => '.info — Portal Informasi & Direktori',
                '.biz' => '.biz — Bisnis & Komersial Internasional',
                '.xyz' => '.xyz — Generasi Baru, Web3 & Startup Populer',
                '.site' => '.site — Website Umum & Publik',
                '.online' => '.online — Bisnis & Layanan Berbasis Online',
                '.website' => '.website — Identitas Website Umum',
                '.top' => '.top — Domain Populer Global',
                '.vip' => '.vip — Layanan & Komunitas Eksklusif',
                '.icu' => '.icu — Domain Populer Ekonomis Global',
                '.link' => '.link — Website Referensi & Link Hub',
                '.click' => '.click — Marketing & Pemasaran Online',
                '.live' => '.live — Media & Penyiaran Live',
                '.pro' => '.pro — Profesional Berlisensi',
                '.space' => '.space — Komunitas & Kreator',
                '.club' => '.club — Komunitas, Klub & Hobi',
                '.world' => '.world — Jangkauan Seluruh Dunia',
            ],

            '💻 Teknologi, AI & Developer' => [
                '.ai' => '.ai — Kecerdasan Buatan & Startup Teknologi',
                '.io' => '.io — Teknologi, Developer & Software',
                '.dev' => '.dev — Khusus Developer & Programmer (Google)',
                '.app' => '.app — Aplikasi Mobile & Web (Google)',
                '.tech' => '.tech — Perusahaan & Portal Teknologi',
                '.cloud' => '.cloud — Komputasi Awan & SaaS',
                '.digital' => '.digital — Agensi & Transformasi Digital',
                '.software' => '.software — Produk & Perangkat Lunak',
                '.systems' => '.systems — Integrasi Sistem & Solusi TI',
                '.network' => '.network — Jaringan Komputer & Sosial',
                '.host' => '.host — Hosting & Server',
                '.data' => '.data — Big Data & Analitik',
                '.bot' => '.bot — Chatbot & Automasi',
                '.code' => '.code — Pemrograman & Open Source',
                '.page' => '.page — Landing Page & Portofolio',
            ],

            '🛍️ E-Commerce, Toko & Bisnis' => [
                '.shop' => '.shop — Toko Online & E-Commerce',
                '.store' => '.store — Toko Ritel & Penjualan Produk',
                '.market' => '.market — Marketplace & Pasar Digital',
                '.company' => '.company — Profil Perusahaan',
                '.agency' => '.agency — Agensi Periklanan / Pemasaran / SEO',
                '.consulting' => '.consulting — Konsultan Profesional',
                '.group' => '.group — Grup Bisnis & Konglomerasi',
                '.ltd' => '.ltd — Perusahaan Perseroan Terbatas',
                '.holdings' => '.holdings — Perusahaan Induk / Holding',
                '.capital' => '.capital — Investasi & Pendanaan Modal',
                '.finance' => '.finance — Keuangan & Perbankan',
                '.fund' => '.fund — Reksadana & Penggalangan Dana',
                '.loans' => '.loans — Pinjaman & Pembiayaan',
                '.bank' => '.bank — Lembaga Perbankan Resmi',
                '.trade' => '.trade — Perdagangan & Ekspor-Impor',
                '.solutions' => '.solutions — Solusi Bisnis & Layanan',
                '.services' => '.services — Jasa & Pelayanan Bisnis',
                '.management' => '.management — Layanan Manajemen',
            ],

            '🎓 Edukasi & Institusi Akademik' => [
                '.edu' => '.edu — Perguruan Tinggi / Universitas Global',
                '.ac.id' => '.ac.id — Kampus / Universitas Indonesia',
                '.sch.id' => '.sch.id — Sekolah Resmi Indonesia',
                '.academy' => '.academy — Lembaga Pelatihan & Akademi',
                '.school' => '.school — Institusi Sekolah',
                '.education' => '.education — Portal & Layanan Pendidikan',
                '.training' => '.training — Kursus & Pelatihan Kerja',
                '.institute' => '.institute — Institut Riset & Studi',
                '.university' => '.university — Pendidikan Tinggi',
                '.study' => '.study — Materi & Bimbingan Belajar',
                '.courses' => '.courses — Kursus Online & Sertifikasi',
                '.gov' => '.gov — Instansi Pemerintah (Amerika & Global)',
                '.mil' => '.mil — Militer Internasional',
                '.int' => '.int — Organisasi Perjanjian Internasional',
            ],

            '📰 Media, Berita, Blog & Konten' => [
                '.blog' => '.blog — Weblog, Jurnalistik & Penulis',
                '.news' => '.news — Berita & Kantor Berita',
                '.media' => '.media — Media Massa & Agensi Berita',
                '.press' => '.press — Pers & Siaran Publik',
                '.today' => '.today — Berita Terkini Harian',
                '.journal' => '.journal — Jurnal Publikasi & Artikel',
                '.tv' => '.tv — Televisi & Media Video',
                '.radio' => '.radio — Penyiaran Radio & Audio',
                '.video' => '.video — Platform Konten Video',
                '.buzz' => '.buzz — Berita Hangat & Tren Viral',
                '.report' => '.report — Laporan Investigasi & Riset',
            ],

            '🎨 Kreatif, Gaya Hidup, Properti & Travel' => [
                '.design' => '.design — Desain Grafis, UI/UX & Arsitektur',
                '.art' => '.art — Karya Seni & Galeri',
                '.studio' => '.studio — Studio Foto, Musik & Produksi',
                '.photo' => '.photo — Fotografi',
                '.photography' => '.photography — Jasa Fotografer Profesional',
                '.music' => '.music — Industri Musik & Artis',
                '.fashion' => '.fashion — Mode & Pakaian',
                '.beauty' => '.beauty — Kecantikan & Kosmetik',
                '.style' => '.style — Gaya Hidup & Tren',
                '.travel' => '.travel — Wisata & Pariwisata',
                '.tours' => '.tours — Paket Wisata & Biro Perjalanan',
                '.hotel' => '.hotel — Penginapan & Perhotelan',
                '.restaurant' => '.restaurant — Kuliner & Restoran',
                '.cafe' => '.cafe — Kafe & Kedai Kopi',
                '.food' => '.food — Resep & Ulasan Makanan',
                '.fitness' => '.fitness — Kebugaran & Gym',
                '.health' => '.health — Kesehatan & Medis',
                '.clinic' => '.clinic — Klinik Kesehatan',
                '.doctor' => '.doctor — Dokter & Praktisi Medis',
                '.dental' => '.dental — Dokter Gigi',
                '.properties' => '.properties — Properti & Agen Real Estat',
                '.estate' => '.estate — Perumahan & Real Estate',
                '.house' => '.house — Rumah & Konstruksi',
                '.auto' => '.auto — Otomotif & Kendaraan',
                '.car' => '.car — Mobil & Transportasi',
                '.law' => '.law — Hukum & Pengacara',
                '.lawyer' => '.lawyer — Kantor Hukum & Advokat',
            ],

            '🌏 Negara Asia & Pasifik (ccTLD)' => [
                '.my' => '.my — Malaysia',
                '.com.my' => '.com.my — Komersial Malaysia',
                '.edu.my' => '.edu.my — Pendidikan Malaysia',
                '.sg' => '.sg — Singapura',
                '.com.sg' => '.com.sg — Bisnis Singapura',
                '.th' => '.th — Thailand',
                '.co.th' => '.co.th — Bisnis Thailand',
                '.vn' => '.vn — Vietnam',
                '.ph' => '.ph — Filipina',
                '.com.ph' => '.com.ph — Bisnis Filipina',
                '.jp' => '.jp — Jepang',
                '.co.jp' => '.co.jp — Perusahaan Jepang',
                '.kr' => '.kr — Korea Selatan',
                '.co.kr' => '.co.kr — Bisnis Korea Selatan',
                '.cn' => '.cn — Republik Rakyat Tiongkok (China)',
                '.com.cn' => '.com.cn — Komersial China',
                '.hk' => '.hk — Hong Kong',
                '.tw' => '.tw — Taiwan',
                '.in' => '.in — India',
                '.co.in' => '.co.in — Bisnis India',
                '.pk' => '.pk — Pakistan',
                '.bd' => '.bd — Bangladesh',
                '.au' => '.au — Australia',
                '.com.au' => '.com.au — Bisnis Australia',
                '.nz' => '.nz — Selandia Baru (New Zealand)',
                '.co.nz' => '.co.nz — Bisnis Selandia Baru',
                '.ae' => '.ae — Uni Emirat Arab (Dubai/Abu Dhabi)',
                '.sa' => '.sa — Arab Saudi',
                '.qa' => '.qa — Qatar',
                '.kw' => '.kw — Kuwait',
                '.tr' => '.tr — Turki',
                '.com.tr' => '.com.tr — Bisnis Turki',
                '.il' => '.il — Israel',
                '.kz' => '.kz — Kazakhstan',
                '.lk' => '.lk — Sri Lanka',
                '.np' => '.np — Nepal',
                '.kh' => '.kh — Kamboja',
                '.la' => '.la — Laos',
                '.mm' => '.mm — Myanmar',
                '.bn' => '.bn — Brunei Darussalam',
            ],

            '🌍 Negara Eropa (ccTLD)' => [
                '.uk' => '.uk — Inggris Raya (United Kingdom)',
                '.co.uk' => '.co.uk — Komersial Inggris',
                '.org.uk' => '.org.uk — Organisasi Inggris',
                '.de' => '.de — Jerman (Germany)',
                '.fr' => '.fr — Prancis (France)',
                '.it' => '.it — Italia',
                '.es' => '.es — Spanyol (Spain)',
                '.nl' => '.nl — Belanda (Netherlands)',
                '.ru' => '.ru — Federasi Rusia',
                '.ch' => '.ch — Swiss (Switzerland)',
                '.se' => '.se — Swedia (Sweden)',
                '.no' => '.no — Norwegia (Norway)',
                '.dk' => '.dk — Denmark',
                '.fi' => '.fi — Finlandia (Finland)',
                '.pl' => '.pl — Polandia (Poland)',
                '.cz' => '.cz — Republik Ceko (Czechia)',
                '.at' => '.at — Austria',
                '.be' => '.be — Belgia (Belgium)',
                '.ie' => '.ie — Irlandia (Ireland)',
                '.pt' => '.pt — Portugal',
                '.gr' => '.gr — Yunani (Greece)',
                '.ua' => '.ua — Ukraina',
                '.ro' => '.ro — Rumania',
                '.hu' => '.hu — Hungaria',
                '.bg' => '.bg — Bulgaria',
                '.hr' => '.hr — Kroasia (Croatia)',
                '.rs' => '.rs — Serbia',
                '.sk' => '.sk — Slowakia',
                '.si' => '.si — Slovenia',
                '.ee' => '.ee — Estonia',
                '.lv' => '.lv — Latvia',
                '.lt' => '.lt — Lithuania',
                '.is' => '.is — Islandia (Iceland)',
                '.lu' => '.lu — Luksemburg',
                '.eu' => '.eu — Uni Eropa (European Union)',
            ],

            '🌎 Negara Amerika Utara & Latin (ccTLD)' => [
                '.us' => '.us — Amerika Serikat (United States)',
                '.ca' => '.ca — Kanada (Canada)',
                '.mx' => '.mx — Meksiko (Mexico)',
                '.com.mx' => '.com.mx — Bisnis Meksiko',
                '.br' => '.br — Brasil (Brazil)',
                '.com.br' => '.com.br — Bisnis Brasil',
                '.ar' => '.ar — Argentina',
                '.com.ar' => '.com.ar — Bisnis Argentina',
                '.cl' => '.cl — Chili (Chile)',
                '.co' => '.co — Kolombia (Sering dipakai Company Global)',
                '.com.co' => '.com.co — Bisnis Kolombia',
                '.pe' => '.pe — Peru',
                '.com.pe' => '.com.pe — Bisnis Peru',
                '.uy' => '.uy — Uruguay',
                '.py' => '.py — Paraguay',
                '.ec' => '.ec — Ekuador',
                '.pa' => '.pa — Panama',
                '.cr' => '.cr — Kosta Rika',
                '.do' => '.do — Republik Dominika',
                '.pr' => '.pr — Puerto Riko',
            ],

            '🌍 Negara Afrika (ccTLD)' => [
                '.za' => '.za — Afrika Selatan (South Africa)',
                '.co.za' => '.co.za — Bisnis Afrika Selatan',
                '.ng' => '.ng — Nigeria',
                '.com.ng' => '.com.ng — Bisnis Nigeria',
                '.eg' => '.eg — Mesir (Egypt)',
                '.ke' => '.ke — Kenya',
                '.co.ke' => '.co.ke — Bisnis Kenya',
                '.ma' => '.ma — Maroko (Morocco)',
                '.gh' => '.gh — Ghana',
                '.tz' => '.tz — Tanzania',
                '.ug' => '.ug — Uganda',
                '.dz' => '.dz — Aljazair (Algeria)',
                '.tn' => '.tn — Tunisia',
                '.et' => '.et — Ethiopia',
            ],

            '🏝️ ccTLD Populer & Spesial Branding Global' => [
                '.me' => '.me — Personal Branding (Montenegro)',
                '.to' => '.to — URL Shortener & Tech (Tonga)',
                '.cc' => '.cc — Populer Internasional (Cocos Islands)',
                '.gg' => '.gg — Gaming & Esports (Guernsey)',
                '.ly' => '.ly — URL Shortener & App (Libya)',
                '.fm' => '.fm — Musik & Radio (Mikronesia)',
                '.ws' => '.ws — Website Global (Samoa)',
                '.vc' => '.vc — Venture Capital (Saint Vincent)',
                '.sh' => '.sh — Shell / Terminal Script (Saint Helena)',
                '.im' => '.im — Instant Messaging (Isle of Man)',
                '.so' => '.so — Tech / Notion-style (Somalia)',
                '.ms' => '.ms — Microsoft-related (Montserrat)',
            ],
        ];
    }

    /**
     * Get a flattened associative array of all TLDs [".ext" => "description"].
     *
     * @return array<string, string>
     */
    public static function flatCatalog(): array
    {
        $flat = [];
        foreach (self::groupedCatalog() as $group => $items) {
            foreach ($items as $ext => $label) {
                $flat[$ext] = $label;
            }
        }

        return $flat;
    }

    /**
     * Return just the list of extension strings (e.g. ['.id', '.com', ...]).
     *
     * @return array<string>
     */
    public static function allExtensions(): array
    {
        return array_keys(self::flatCatalog());
    }
}
