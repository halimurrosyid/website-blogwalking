<?php

namespace App\Services;

class BankListService
{
    /**
     * Grouped list of all commercial banks, digital banks, regional development banks (BPD),
     * and official e-wallets operating in Indonesia.
     *
     * @return array<string, array<string, string>>
     */
    public static function groupedList(): array
    {
        return [
            'Bank BUMN & Syariah Utama' => [
                'BCA' => 'Bank Central Asia (BCA)',
                'Mandiri' => 'Bank Mandiri',
                'BRI' => 'Bank Rakyat Indonesia (BRI)',
                'BNI' => 'Bank Negara Indonesia (BNI)',
                'BSI' => 'Bank Syariah Indonesia (BSI)',
                'BTN' => 'Bank Tabungan Negara (BTN)',
            ],
            'Bank Digital Populer' => [
                'Seabank' => 'SeaBank Indonesia',
                'Jago' => 'Bank Jago',
                'BNC' => 'Bank Neo Commerce (BNC / Neobank)',
                'Blu' => 'Blu by BCA Digital',
                'BTPN_Jenius' => 'Bank BTPN / Jenius',
                'Allo' => 'Allo Bank Indonesia',
                'Superbank' => 'Superbank',
                'Raya' => 'Bank Raya Indonesia',
                'TMRW' => 'TMRW by UOB Indonesia',
            ],
            'Bank Swasta Nasional' => [
                'CIMB' => 'Bank CIMB Niaga',
                'Permata' => 'Bank Permata',
                'Danamon' => 'Bank Danamon',
                'Panin' => 'Bank Panin',
                'OCBC' => 'Bank OCBC NISP',
                'Mega' => 'Bank Mega',
                'Maybank' => 'Bank Maybank Indonesia',
                'Sinarmas' => 'Bank Sinarmas',
                'Muamalat' => 'Bank Muamalat',
                'Bukopin' => 'KB Bukopin',
                'MNC' => 'Bank MNC',
                'Victoria' => 'Bank Victoria',
                'Maspion' => 'Bank Maspion',
                'Ina' => 'Bank Ina Perdana',
                'ArthaGraha' => 'Bank Artha Graha Internasional',
                'Ganesha' => 'Bank Ganesha',
                'Nobu' => 'Bank Nationalnobu (Nobu Bank)',
                'Mayapada' => 'Bank Mayapada',
                'Mestika' => 'Bank Mestika Dharma',
                'Shinhan' => 'Bank Shinhan Indonesia',
                'Woori' => 'Bank Woori Saudara',
                'IBK' => 'Bank IBK Indonesia',
                'Commonwealth' => 'Bank Commonwealth',
                'UOB' => 'Bank UOB Indonesia',
                'StandardChartered' => 'Standard Chartered Bank',
                'HSBC' => 'HSBC Indonesia',
            ],
            'Bank Pembangunan Daerah (BPD Seluruh Indonesia)' => [
                'BPD_DKI' => 'Bank DKI',
                'BPD_BJB' => 'Bank BJB (Jawa Barat & Banten)',
                'BPD_Jateng' => 'Bank Jateng',
                'BPD_Jatim' => 'Bank Jatim',
                'BPD_DIY' => 'Bank BPD DIY (Yogyakarta)',
                'BPD_Bali' => 'Bank BPD Bali',
                'BPD_Sumut' => 'Bank Sumut',
                'BPD_Nagari' => 'Bank Nagari (BPD Sumatera Barat)',
                'BPD_RiauKepri' => 'Bank Riau Kepri Syariah',
                'BPD_SumselBabel' => 'Bank Sumsel Babel',
                'BPD_Lampung' => 'Bank Lampung',
                'BPD_Jambi' => 'Bank Jambi',
                'BPD_Bengkulu' => 'Bank Bengkulu',
                'BPD_Kalsel' => 'Bank Kalsel',
                'BPD_Kalbar' => 'Bank Kalbar',
                'BPD_Kaltimtara' => 'Bank Kaltimtara',
                'BPD_Kalteng' => 'Bank Kalteng',
                'BPD_Sulselbar' => 'Bank Sulselbar',
                'BPD_SulutGo' => 'Bank SulutGo',
                'BPD_Sulteng' => 'Bank Sulteng',
                'BPD_Sultra' => 'Bank Sultra',
                'BPD_NTB' => 'Bank NTB Syariah',
                'BPD_NTT' => 'Bank NTT',
                'BPD_MalukuMalut' => 'Bank Maluku Malut',
                'BPD_Papua' => 'Bank Papua',
            ],
            'Dompet Digital / E-Wallet Resmi' => [
                'DANA' => 'DANA (Dompet Digital)',
                'GOPAY' => 'GoPay (Gojek)',
                'OVO' => 'OVO',
                'SHOPEEPAY' => 'ShopeePay',
                'LINKAJA' => 'LinkAja',
            ],
            'Lainnya' => [
                'LAINNYA' => 'Bank Lain / Koperasi / BPR Lainnya',
            ],
        ];
    }

    /**
     * Flat key => label array of all banks.
     *
     * @return array<string, string>
     */
    public static function flatList(): array
    {
        $flat = [];
        foreach (self::groupedList() as $group => $banks) {
            foreach ($banks as $code => $name) {
                $flat[$code] = $name;
            }
        }

        return $flat;
    }

    /**
     * Get bank label by code or return original string if custom.
     */
    public static function getLabel(?string $code): string
    {
        if (empty($code)) {
            return '-';
        }

        $all = self::flatList();

        return $all[$code] ?? $code;
    }
}
