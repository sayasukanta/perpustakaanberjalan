<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>raModal Popup dengan Efek Zoom-In</title>
    <!-- Tambahkan CSS untuk styling raModal -->
    <style>
        /* Gaya dasar untuk raModal */
        .raModal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            opacity: 0.95;
            
        }

        .raModal-content {
            background-color: #fff;
            
            opacity: 0.6;
            margin: 10% auto; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 1000px;
            transform: scale(0); /* Awal dengan ukuran kecil */
            animation: zoomIn 6.5s forwards; /* Efek zoom-in */
        }

        .raModal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #333;
        }

        .raModal-body {
            display: flex;
            align-items: center;
            gap: 20px; /* Jarak antara gambar dan teks */
            
        }

        .raModal-body img {
            max-width: 40%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .raModal-body .text {
            font-size: 18px;
            color: #666;
            line-height: 1.5;
        }

        /* Animasi untuk efek zoom-in */
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Animasi untuk efek fadeaway saat raModal ditutup */
        @keyframes fadeaway {
            from { opacity: 1; }
            to { opacity: 0; transform: scale(0.8); }
        }
    </style>
</head>
<body>

    <!-- raModal -->
    <div id="myraModal" class="raModal">
        <div class="raModal-content">
            <div class="raModal-header">
            </div>
            <div class="raModal-body">
            <img src="/hi-pictures.jpg" alt="">
                <div class="text">
                <h2>Hai....!</h2>
    <p>Nama saya Chika humaira, mahasiswi Universitas Bakrie angkatan 2021.</p>
    <p>Laman ini adalah manifestasi dari penelitian saya yang berjudul 
    "IMPLEMENTASI SISTEM REKOMENDASI BUKU BERBASIS KONTEN MENGGUNAKAN VECTOR SPACE MODEL DAN
    SIMILARITY MEASURE UNTUK PENGELOLAAN ANTREAN DAN SIRKULASI DI PERPUSTAKAAN BERJALAN".
    </p>
    <p>
    Perpustakaan Berjalan merupakan inisiatif yang bertujuan untuk
    meningkatkan literasi melalui penyediaan buku-buku fisik yang dapat dipinjam
    secara estafet ke seluruh Indonesia. Penelitian ini bertujuan untuk
    memanfaatkan data peminjaman buku menjadi dataset publik dan sistem
    rekomendasi buku yang sesuai dengan preferensi dan kebutuhan pengguna
    Perpustakaan Berjalan. Penelitian dimulai dari studi literatur, penyusunan
    dataset yang meliputi pengumpulan data melalui survei dan dengan teknik data
    acquision,
     melakukan
     data
     preprocessing,
     mengembangkan
     sistem
    rekomendasi buku dengan menggunakan metode content-based filtering,
    vector space model seperti TF-IDF, cosine similarity, dan evaluasi precision,
    hingga penyusunan tugas akhir.            
        </p>                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="/js/jquery-3.6.0.min.js"></script>
    <!-- Script untuk menampilkan raModal dengan efek zoom-in dan menutup dengan fadeaway -->
    <script>
        $(document).ready(function(){
            // Tampilkan raModal langsung saat halaman dimuat
            $("#myraModal").fadeIn();

            // Ketika tombol close diklik, sembunyikan raModal dengan efek fadeaway
            $(".close").click(function(){
                $(".raModal-content").css("animation", "fadeaway 0.5s forwards");
                setTimeout(function() {
                    $("#myraModal").fadeOut();
                    $(".raModal-content").css("animation", "zoomIn 0.5s forwards");
                }, 500);
            });

            // Ketika area luar raModal diklik, sembunyikan raModal dengan efek fadeaway
            $(window).click(function(event){
                if ($(event.target).is("#myraModal")) {
                    $(".raModal-content").css("animation", "fadeaway 0.5s forwards");
                    setTimeout(function() {
                        $("#myraModal").fadeOut();
                        $(".raModal-content").css("animation", "zoomIn 0.5s forwards");
                    }, 500);
                }
            });
            
        });
    </script>

</body>
</html>
