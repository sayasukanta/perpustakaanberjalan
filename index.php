<?php
$url = $_SERVER['REQUEST_URI'];
$servername = "localhost";
$username = "root";
$password = "6508460";
$dbname = "koleksi_buku";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengambil data untuk dropdown
$sql = "SELECT DISTINCT judul1 FROM v_similarity_all order by judul1";
$result = $conn->query($sql);



$judul1 = '';
$searchResult = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul1 = $_POST['judul1'];

    // Mengambil data berdasarkan judul1
    $sql = "SELECT * FROM v_similarity_all WHERE judul1 = ? limit 20";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $judul1);
    $stmt->execute();
    $searchResult = $stmt->get_result();
    $stmt->close();

}
if (isset($_GET['kode'])) {
    // Mengambil data riwayat peminjam
    $k = $_GET[kode];
    $sql6 = "SELECT * FROM riwayat_peminjaman WHERE kode = ?";
    $stmt6 = $conn->prepare($sql6);
    $stmt6->bind_param("s", $kode);
    $stmt6->execute();
    $searchResult6 = $stmt6->get_result();
    $stmt6->close();
}

// Ambil data riwayat peminjaman
$sql_riwayat = "SELECT kode,judul, username, status,tgl_pinjam,tgl_kembali FROM v_riwayat_peminjaman ";
if($_GET['s']=='top'){
    $sql_riwayat .="where status in('Queue','Borrowing','Borrowed')";
}
else{
    $sql_riwayat .="where status in('Queue','Borrowing')";
}
$result_riwayat = $conn->query($sql_riwayat);

$riwayat_peminjaman = [];
if ($result_riwayat->num_rows > 0) {
    while($row_riwayat = $result_riwayat->fetch_assoc()) {
        $riwayat_peminjaman[$row_riwayat['judul']][] = $row_riwayat;
    }
}

// Ambil data detail buku dgn genre tertentu
$sql_book_genre = "SELECT * FROM v_book_genre ";
$result_book_genre = $conn->query($sql_book_genre);

$bookGenre = [];
if ($result_book_genre->num_rows > 0) {
    while($row_book_genre = $result_book_genre->fetch_assoc()) {
        $bookGenre[$row_book_genre['genre']][] = $row_book_genre;
    }
}


// Buku populer berdasarkan jumlah peminjam
$sql_pop = "SELECT SQL_CALC_FOUND_ROWS `kode`, `judul`, COUNT(`username`) as jml_peminjam
                FROM `v_riwayat_peminjaman`
                GROUP BY `kode`, `judul`
                order by jml_peminjam desc";
$result_pop = $conn->query($sql_pop);



// Buku populer berdasarkan jumlah peminjam
$sql_genre = "SELECT * FROM `v_rekap_book_genre` ";
$result_genre = $conn->query($sql_genre);

$rating="<span class=\"tooltip\" title=\"Nilai rating diambil dari situs https://www.goodreads.com/ pada bulan Juli 2024.\">RATING*</span>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Finding book based on Content Based Similarities method</title>
    <link rel="shortcut icon" href="/owl.ico" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: auto;
            overflow: hidden;
            float: center;
        }
        header {
            background: #50b3a2;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #e8491d 3px solid;
            
        }
        header a {
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
        }
        header ul {
            padding: 0;
            list-style: none;
        }
        header li {
            float: left;
            display: inline;
            padding: 0 20px 0 20px;
        }
        header #branding {
            float: center;
        }
        header #branding h1 {
            margin: 0;
        }
        header #branding h2 {
            margin: 0;
            float: center;
        }
        header #branding h3 {
            margin: 0;
            float: center;
        }
        header nav {
            float: right;
            margin-top: 10px;
        }
        form {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form label {
            display: block;
            margin-bottom: 10px;
        }
        form select, form input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #50b3a2;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px;
        }
        form input[type="submit"] {
            background: #50b3a2;
            color: #fff;
            border: 0;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        form input[type="submit"]:hover {
            background: #45a089;
        }
        .message {
            background: #e8491d;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .popup h2 {
            margin-top: 0;
        }
        .popup .close {
            background: #e8491d;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        
    
        .popup1 {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .popup1 h2 {
            margin-top: 0;
        }
        .popup1 .close {
            background: #e8491d;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 1200px;
            transform: scale(0); /* Awal dengan ukuran kecil */
            animation: zoomIn 2.5s forwards; /* Efek zoom-in */
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

            /* Animasi untuk efek zoom-in */
            @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 12px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .tooltip {
        position: relative;
        display: inline-block;
        border-bottom: 3px dotted red; /* Jika Anda ingin menggunakan titik di bawah teks yang dapat dihover */
    }

    </style>



    <script>
        function showPopup(sinopsis) {
            document.getElementById('popup-content').innerText = sinopsis;
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        function showPopup1(antrian_doc2) {
            document.getElementById('popup-content1').innerText = antrian_doc2;
            document.getElementById('popup1').style.display = 'block';
            document.getElementById('overlay1').style.display = 'block';
        }


        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        function closePopup1() {
            document.getElementById('popup1').style.display = 'none';
            document.getElementById('overlay1').style.display = 'none';
        }

    </script>

</head>

<body>
<header>
        <div class="container">
            <div id="branding">
            <h2><center>DAFTAR PENCARIAN BUKU DENGAN KONTEN SERUPA</center></h2>
<h3><center>BERDASARKAN PENDEKATAN MODEL <i>CONTENT BASED FILTERING</i></center></h3>

            </div>

            <nav>
                <ul>
                    <li><a href="/#">Home</a></li>
                    <li><a href="/?s=top#">Populer</a></li>
                    <li><a href="/?s=genre#">Genre</a></li>
                    <li>
                    <a href="/?s=about#">About</a>    
                    </li>

                </ul>
            </nav>
            
        </div>
    </header>    
<?php
switch ($_GET['s']) {
    default:
      //code block
?>   

    <div class="container">
        <form method="post" action="">
            <label for="judul1">Pilih Judul:</label>
            <select name="judul1" id="judul1">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $selected = ($row["judul1"] == $judul1) ? "selected" : "";
                        echo "<option value='" . htmlspecialchars($row["judul1"]) . "' $selected>" .htmlspecialchars($row["judul1"]) . "</option>";
                    }
                } else {
                    echo "<option value=''>Tidak ada judul tersedia</option>";
                }
                ?>
            </select>
            <input type="submit" value="Cari">
        </form>

        <?php
        if ($searchResult && $searchResult->num_rows > 0) {
            $sql1 = "SELECT a.*, (select count(*) from riwayat_peminjaman where kode=a.kode and status in('Queue','Borrowing') group by kode) as jml_peminjam1 FROM koleksi_buku a where a.judul like \"%".addslashes($judul1)."%\"  limit 1";
            $result1 = $conn->query($sql1);
            if($result1->num_rows > 0){
            $result1_data = $result1->fetch_assoc(); 
            $antrian ="(<a href='#' onclick='showModal(\"" . htmlspecialchars($result1_data['judul1']). "\")'>" . htmlspecialchars($result1_data['antrian_doc1']). " antrian</a>)";
        
        }
            else{
                $antrian = " (Tidak ada antrian)";
            }
           $c= "style='background-color: #50b000;'";
            echo "<table>
            <tr>
                <th $c>No.</th>
                <th $c>BUKU YANG DICARI</th>
                <th $c>PENULIS</th>
                <th $c>JML. HLM</th>
                <th $c>$rating</th>
                <th $c>GENRE</th>
                <th $c>SINOPSIS</th>
                <th $c>JML.ANTRIAN</th>
                <th $c>COVER</th>
                <th $c>SIMILARITY(%)</th>
            </tr>";
        $n = 1;
        echo "<tr>
                <td>" . $n . "</td>
                <td>" . htmlspecialchars($result1_data['judul']) . "</td>
                <td>" . htmlspecialchars($result1_data["penulis"]) . "</td>
                <td>" . htmlspecialchars($result1_data["jml_hlm"]) . "</td>
                <td>" . htmlspecialchars($result1_data["rating"]) . "</td>
                <td>" . htmlspecialchars($result1_data["genre"]) . "</td>
                <td>  
                <a href=\"javascript:void(0);\" onclick='showPopup(\"".htmlspecialchars($result1_data['sinopsis'], ENT_QUOTES, 'UTF-8')."\")'>Lihat Sinopsis</a> 
                 </td>
                <td style='text-align:center;'>  
                <a href='#' onclick='showModal(\"" . htmlspecialchars($result1_data["judul"]). "\")'>" . $result1_data["jml_peminjam1"]. "</a>
                 </td>
                <td><img src='cover/" . $result1_data["cover"] . "' alt='Cover'></td>
                <td>100</td>
            </tr>";
        echo "<table>";

        
        echo "<hr><h3>Berikut rekomendasi 20 buku lainnya dengan konten serupa: </h3>";
        
            echo "<table>
                    <tr>
                        <th>No.</th>
                        <th>REKOMENDASI BUKU SERUPA</th>
                        <th>PENULIS</th>
                        <th>JML. HLM</th>
                        <th>$rating</th>
                        <th>GENRE</th>
                        <th>SINOPSIS</th>
                        <th>JML. ANTRIAN</th>
                        <th>COVER</th>
                        <th>SIMILARITY(%)</th>
                    </tr>";
            $n = 1;
            while($row = $searchResult->fetch_assoc()) {
                 
                echo "<tr>
                        <td>" . $n . "</td>
                        <td>" . htmlspecialchars($row["judul2"]) . "</td>
                        <td>" . htmlspecialchars($row["penulis"]) . "</td>
                        <td>" . htmlspecialchars($row["jml_hlm"]) . "</td>
                        <td>" . htmlspecialchars($row["rating"]) . "</td>
                        <td>" . htmlspecialchars($row["genre"]) . "</td>
                        <td>  
                        <a href=\"javascript:void(0);\" onclick='showPopup(\"".htmlspecialchars($row['sinopsis'])."\")'>Lihat Sinopsis</a> 
                         </td>
                        <td style='text-align:center;'>  
                        <a href='#' onclick='showModal(\"" . htmlspecialchars($row["judul2"]). "\")'>" . $row["antrian_doc2"]. "</a>
                         </td>
                        <td><img src='cover/" . $row["cover"] . "' alt='Cover'></td>
                        <td>" . number_format(($row["similarity"])*100,4) . "</td>
                    </tr>";
                    /*<td><a href ='update_form.php?id=".$row["doc2"]."' target='_blank'> Update</a> 
                    </td>*/;
                $n++;    
            }
            echo "</table>";
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
            echo "Tidak ada hasil ditemukan untuk judul: " . htmlspecialchars($judul1);
        }
        ?>
    </div>

<?php
  break;
    case 'top':
      //code block;
echo "<div class='container'>";
 echo "<h3 style='text-align:center;'>DAFTAR BUKU POPULER BERDASARKAN JUMLAH PEMINJAM</h3><br>";
        
 echo "<table>
         <tr>
             <th>No.</th>
             <th>Kode</th>
             <th>Judul buku</th>
             <th>Jml Peminjam</th>
         </tr>";
 $n = 1;
    if ($result_pop->num_rows > 0) {
        while($row_pop = $result_pop->fetch_assoc()) {
             
     echo "<tr>
             <td>" . $n . "</td>
             <td>" . htmlspecialchars($row_pop["kode"]) . "</td>
             <td>" . htmlspecialchars($row_pop["judul"]) . "</td>
             <td style='text-align:center;'>  
             <a href='#' onclick='showModal(\"" . htmlspecialchars($row_pop["judul"]). "\")'>" . $row_pop["jml_peminjam"]. "</a>
              </td>
         </tr>";
     $n++;    
        }
    }
 else{
echo "<tr><td colspan=4>---- Tidak ada data --- </td></tr>";
 }
 echo "</table>";
echo "</div>";
 break;
?>

<?php
   case 'genre':
    //code block
    echo "<div class='container' >";
    echo "<h3 style='text-align:center;'>DAFTAR JUMLAH BUKU DENGAN GENRE TERBANYAK</h3><br>";
           
    echo "<table>
            <tr>
                <th>No.</th>
                <th>GENRE</th>
                <th>JML. BUKU</th>
            </tr>";
    $n = 1;
       if ($result_genre->num_rows > 0) {
           while($row_genre = $result_genre->fetch_assoc()) {
                
        echo "<tr>
                <td>" . $n . "</td>
                <td>" . htmlspecialchars($row_genre["genre"]) . "</td>
                <td style='text-align:center;'>  
                <a href='/?s=genb&genre=" . htmlspecialchars($row_genre["genre"]) . "#'>" . $row_genre["jml_buku"]. "</a>
                 </td>
            </tr>";
        $n++;    
           }
       }
    else{
   echo "<tr><td colspan=4>---- Tidak ada data --- </td></tr>";
    }
    echo "</table>";
   echo "</div>";
    break;
?>

<?php
   case 'genb':
    //code block
    echo "<div class='container' >";
    echo "<h3 style='text-align:center;'>DAFTAR BUKU DENGAN KATEGORI GENRE ".strtoupper($_GET['genre'])."</h3><br>";
           
    echo "<table>
            <tr>
                <th>No.</th>
                <th>JUDUL BUKU</th>
                <th>PENULIS</th>
                <th><span class=\"tooltip\" title=\"Nilai rating diambil dari situs https://www.goodreads.com/\">RATING</span></th>
                <th>JML. HAL</th>
                <th>SINOPSIS</th>
            </tr>";
    $n = 1;
    $sql_book_genre = "SELECT * FROM v_book_genre where genre like '".$_GET['genre']."' ";
    $result_book_genre = $conn->query($sql_book_genre);
       if ($result_book_genre->num_rows > 0) {
           while($row_genb = $result_book_genre->fetch_assoc()) {
                
        echo "<tr>
                <td>" . $n . "</td>
                <td>" . htmlspecialchars($row_genb["judul"]) . "</td>
                <td>" . htmlspecialchars($row_genb["penulis"]) . "</td>
                <td>" . htmlspecialchars($row_genb["rating"]) . "</td>
                <td>" . htmlspecialchars($row_genb["jml_hlm"]) . "</td>
                <td><a href=\"javascript:void(0);\" onclick=\"showPopup('".htmlspecialchars($row_genb['sinopsis'])."')\">Sinopsis</a> 
                         </td>

                </tr>";
        $n++;    
           }
       }
    else{
   echo "<tr><td colspan=4>---- Tidak ada data --- </td></tr>";
    }
    echo "</table>";
   echo "</div>";
    break;
?>
<?php
    case 'about':
        
        $images = glob("cover/*.{jpg,jpeg,gif,png,bmp,webp}", GLOB_BRACE);
        foreach ($images as $image) {
            echo "<img src='cover/" . rawurlencode(basename($image)) . "'>";

        }
include "modal.php";             
      
  }
?>

<!-- Footer -->
<div id="popup" class="popup">
        <h2>Sinopsis</h2>
        <p id="popup-content"></p>
        <button class="close" onclick="closePopup()">Tutup</button>
    </div>
    <div id="popup1" class="popup">
        <h2>Riwayat Peminjaman</h2>
        <p id="popup-content1"></p>
        <button class="close" onclick="closePopup1()">Tutup</button>
    </div>


    <div id="overlay" class="overlay" onclick="closePopup()"></div>
    <div id="overlay1" class="overlay" onclick="closePopup1()"></div>



    </body>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Riwayat Peminjaman</h2>
            <div id="modalBody"></div>
        </div>
    </div>

    <div id="myModal1" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle1">Riwayat Peminjaman</h2>
            <div id="modalBody1"></div>
        </div>
    </div>

    <script>
    // Data riwayat peminjaman dalam bentuk JSON
    var riwayatPeminjaman = <?php echo json_encode($riwayat_peminjaman); ?>;

    function showModal(judul) {
        var modal = document.getElementById("myModal");
        var modalTitle = document.getElementById("modalTitle");
        var modalBody = document.getElementById("modalBody");
        <?php if($_GET['s']=='top'){ ?>
            modalTitle.innerHTML = "Daftar Peminjam Buku : " + judul;
        <?php } else { ?>
            modalTitle.innerHTML = "Daftar Antrian Buku : " + judul;  
        <?php } ?>    
        var data = "<table border='1'><tr><th>Username</th><th>Rencana Tgl. Pinjam</th><th>Rencana Tgl. Kembali</th><th>Status</th></tr>";

        if (riwayatPeminjaman[judul]) {
            riwayatPeminjaman[judul].forEach(function(item) {
                data += "<tr><td>" + item.username + "</td><td>" + item.tgl_pinjam + "</td><td>" + item.tgl_kembali + "</td><td>" + item.status + "</td></tr>";
            });
        } else {
            data += "<tr><td colspan='2'>Tidak ada data riwayat peminjaman</td></tr>";
        }

        data += "</table>";
        modalBody.innerHTML = data;

        modal.style.display = "block";
    }

    function showModal1(genre) {
        var modal1 = document.getElementById("myModal1");
        var modalTitle1 = document.getElementById("modalTitle1");
        var modalBody1 = document.getElementById("modalBody1");
            modalTitle1.innerHTML = "Daftar Buku dengan Kategori Genre : " + genre;
   
        var data = "<table border='1'><tr><th>kode</th><th>Judul</th><th>genre</th></tr>";

        if (bookGenre[genre]) {
            bookGenre[genre].forEach(function(tem) {
                data += "<tr><td>" + tem.kode + "</td><td>" + tem.judul + "</td><td>" + tem.genre + "</td></tr>";
            });
        } else {
            data += "<tr><td colspan='2'>Tidak ada data riwayat peminjaman</td></tr>";
        }

        data += "</table>";
        modalBody1.innerHTML = data;

        modal1.style.display = "block";
    }


    function closeModal() {
        var modal = document.getElementById("myModal");
        modal.style.display = "none";
    }

    function closeModal1() {
        var modal1 = document.getElementById("myModal1");
        modal1.style.display = "none";
    }

    // Close the modal if the user clicks anywhere outside of the modal content
    window.onclick = function(event) {
        var modal = document.getElementById("myModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }


   </script>

<?php
$conn->close();
?>
</html>
