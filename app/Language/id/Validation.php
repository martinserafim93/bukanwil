<?php

// Pesan validasi dalam Bahasa Indonesia
return [
    // Rule dasar
    'required'              => 'Kolom {field} wajib diisi.',
    'permit_empty'          => 'Kolom {field} boleh kosong.',

    // Panjang karakter
    'min_length'            => 'Kolom {field} minimal {param} karakter.',
    'max_length'            => 'Kolom {field} tidak boleh melebihi {param} karakter.',
    'exact_length'          => 'Kolom {field} harus berjumlah {param} karakter.',

    // Angka / numerik
    'numeric'               => 'Kolom {field} hanya boleh berisi angka.',
    'integer'               => 'Kolom {field} harus berupa bilangan bulat.',
    'decimal'               => 'Kolom {field} harus berupa angka desimal.',
    'is_natural'            => 'Kolom {field} harus berupa bilangan bulat positif.',
    'is_natural_no_zero'    => 'Kolom {field} harus berupa bilangan bulat lebih besar dari nol.',
    'greater_than'          => 'Nilai kolom {field} harus lebih besar dari {param}.',
    'greater_than_equal_to' => 'Nilai kolom {field} harus lebih besar atau sama dengan {param}.',
    'less_than'             => 'Nilai kolom {field} harus kurang dari {param}.',
    'less_than_equal_to'    => 'Nilai kolom {field} harus kurang dari atau sama dengan {param}.',
    'between'               => 'Nilai kolom {field} harus di antara {param}.',

    // Pencocokan / format
    'regex_match'           => 'Format kolom {field} tidak sesuai.',
    'matches'               => 'Kolom {field} tidak sama dengan kolom {param}.',
    'differs'               => 'Kolom {field} harus berbeda dengan kolom {param}.',
    'in_list'               => 'Kolom {field} harus salah satu dari: {param}.',
    'is_unique'             => 'Nilai kolom {field} sudah terpakai.',

    // Upload berkas
    'uploaded'              => '{field} bukan berkas unggahan yang valid.',
    'max_size'              => 'Ukuran berkas {field} terlalu besar.',
    'mime_in'               => 'Tipe berkas {field} tidak diizinkan.',
    'ext_in'                => 'Ekstensi berkas {field} tidak diizinkan.',
    'is_image'              => 'Berkas {field} harus berupa gambar.',

    // String
    'alpha'                 => 'Kolom {field} hanya boleh berisi huruf.',
    'alpha_numeric'         => 'Kolom {field} hanya boleh berisi huruf dan angka.',
    'alpha_numeric_punct'   => 'Kolom {field} hanya boleh berisi huruf, angka, dan karakter yang diizinkan.',
    'alpha_numeric_space'   => 'Kolom {field} hanya boleh berisi huruf, angka, dan spasi.',
    'alpha_dash'            => 'Kolom {field} hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'valid_url'             => 'Kolom {field} harus berupa URL yang valid.',
    'valid_email'           => 'Kolom {field} harus berupa alamat email yang valid.',
    'valid_emails'          => 'Kolom {field} harus berisi alamat email yang valid.',
    'valid_ip'              => 'Kolom {field} harus berupa alamat IP yang valid.',
    'valid_base64'          => 'Kolom {field} harus berupa string base64 yang valid.',
];
