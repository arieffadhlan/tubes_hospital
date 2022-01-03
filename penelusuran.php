<?php
require_once "lib/EasyRdf.php";

EasyRdf_Namespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
EasyRdf_Namespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
EasyRdf_Namespace::set('db', 'http://dbpedia.org/');
EasyRdf_Namespace::set('dbr', 'http://dbpedia.org/resource/');
EasyRdf_Namespace::set('dbp', 'http://dbpedia.org/property/');
EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
EasyRdf_Namespace::set('owl', 'http://www.w3.org/2002/07/owl#');

$sparql = new EasyRdf_Sparql_Client('http://dbpedia.org/sparql');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Tugas Besar Web Semantik - Pencarian Rumah Sakit" name="description">

    <title>Penelusuran Rumah Sakit</title>
    <link href="assets/img/hospital_icon.ico" rel="icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- CSS -->
    <link href="assets/vendors/aos/aos.css" rel="stylesheet">
    <link href="assets/vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendors/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendors/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendors/simple-datatables/style.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/table.css" rel="stylesheet">
</head>

<body>
<header id="header" class="header fixed-top">
        <div class="container container-xl d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36">
                    <path fill="none" d="M0 0h24v24H0z"/>
                    <path d="M8 20v-6h8v6h3V4H5v16h3zm2 0h4v-4h-4v4zm11 0h2v2H1v-2h2V3a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v17zM11 8V6h2v2h2v2h-2v2h-2v-2H9V8h2z" fill="rgba(1,41,112,1)"/>
                </svg>
                <span class="ps-2">Hospital</span>
            </a>
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link" href="index.php">Beranda</a></li>
                    <li><a class="nav-link active" href="penelusuran.php">Penelusuran</a></li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
        </div>
    </header>

    <section id="hero" class="hero d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">Halaman pencarian rumah sakit</h1>
                    <h2 class="fs-5" data-aos="fade-up" data-aos-delay="400">
                        Anda dapat mencari data rumah sakit yang Anda inginkan di kolom pencarian dengan menginputkan nama negara.
                    </h2>
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="col-md-10 mt-5">
                            <form class="form-inline" role="form" method="POST">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="hospital" placeholder="Input Nama Negara atau Rumah Sakit" aria-label="Input Nama Negara" aria-describedby="button-addon2" autocomplete="off">
                                    <button class="btn text-white" style="background-color: #4154f1;" type="submit" name="submit" id="button-addon2">
                                        <span class="me-1">Cari</span>
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                    <img src="assets/img/search_image.svg" class="img-fluid" alt="Search Image">
                </div>
            </div>
        </div>
    </section>

    <section class="show shadow">
        <div class="container">
            <?php
            if (isset($_POST['hospital'])) {
                $result = $sparql->query(
                    'SELECT DISTINCT ?hospital ?name ?location {' .
                        '?hospital rdf:type dbo:Hospital;' .
                        'dbp:name ?name;' .
                        'dbp:country ?location.' .
                        'FILTER (?location = "' . $_POST['hospital'] . '"@en || ?name = "' . $_POST['hospital'] . '"@en).' .
                        '}'
                );
            ?>
                <div class="entitySection table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="tablePenelusuran">
                        <thead class="text-center">
                            <tr class="table-primary">
                                <th>No</th>
                                <th>Rumah Sakit</th>
                                <th>Lokasi</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($result)) {
                                $no = 1;
                                foreach ($result as $row) {
                                    echo "
                                        <tr>
                                            <td class='text-center'>" . $no++ . "</td>
                                            <td>" . $row->name . "</td>
                                            <td class='text-center'>" . $row->location . "</td>
                                            <td class='d-flex justify-content-center align-items-center'>
                                                <form method='POST' action='detail.php'>
                                                    <input type='hidden' value='" . $row->name . "' name='namaRumahSakit'/>
                                                    <button type='submit' name='hospitalName' class='btn btn-detail d-inline-flex align-items-center justify-content-center align-self-center text-white'>
                                                        <span>Detail</span>
                                                        <i class='bi bi-arrow-right'></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    ";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php 
            } else {
                echo '
                    <div class="container d-flex flex-column justify-content-center align-items-center">
                        <img src="assets/img/not_found.svg" width="300px" class="img-fluid" alt="Data tidak ditemukan">
                        <h2 class="fs-3 mt-5 text-center">Silakan masukkan data yang ingin dicari.</h2>
                    </div>
                ';
            }
            ?>
        </div>
    </section>

    <footer id="footer" class="footer p-0">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-5 col-md-12 footer-info">
                        <a href="index.php" class="logo d-flex align-items-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36">
                                <path fill="none" d="M0 0h24v24H0z"/>
                                <path d="M8 20v-6h8v6h3V4H5v16h3zm2 0h4v-4h-4v4zm11 0h2v2H1v-2h2V3a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v17zM11 8V6h2v2h2v2h-2v2h-2v-2H9V8h2z" fill="rgba(1,41,112,1)"/>
                            </svg>
                            <span class="ps-2">Hospital</span>
                        </a>
                        <p>
                            Rumah sakit adalah institusi pelayanan kesehatan yang menyelenggarakan pelayanan kesehatan perorangan secara paripurna yang menyediakan pelayanan rawat inap, rawat jalan, dan gawat darurat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to top -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendors -->
    <script src="assets/vendors/purecounter/purecounter.js"></script>
    <script src="assets/vendors/aos/aos.js"></script>
    <script src="assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendors/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendors/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendors/simple-datatables/simple-datatables.js"></script>
    <script>
            let tablePenelusuran = document.querySelector('#tablePenelusuran');
            let dataTablePenelusuran = new simpleDatatables.DataTable(tablePenelusuran);
    </script>

    <script src="assets/js/main.js"></script>
</body>

</html>