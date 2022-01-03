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
$namaRumahSakit = $_POST['namaRumahSakit'];

$sparql_query = '
		SELECT ?hospital ?name ?location ?description ?wiki ?lat ?long WHERE {
            ?hospital a dbo:Hospital;
                rdfs:label ?name;
                dbp:country ?location;
                dbo:abstract ?description;
                foaf:isPrimaryTopicOf ?wiki;
                geo:lat ?lat;
                geo:long ?long.
            FILTER langMatches(lang(?name), "EN")
            FILTER langMatches(lang(?description), "EN")
            FILTER(regex(str(?name),"' . $namaRumahSakit . '"))
		}
    ';

$result = $sparql->query($sparql_query);

$detail = [];
foreach ($result as $row) {
    $detail = [
        'hospital' => $row->hospital,
        'name' => $row->name,
        'location' => $row->location,
        'description' => $row->description,
        'wiki' => $row->wiki,
        'lat' => $row->lat,
        'long' => $row->long
    ];
    break;
}

EasyRdf_Namespace::setDefault('og');

$wiki = EasyRdf_Graph::newAndLoad($detail['wiki']);
$foto_url = $wiki->image;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Tugas Besar Web Semantik - Pencarian Rumah Sakit" name="description">

    <title>Detail Rumah Sakit</title>
    <link href="assets/img/hospital_icon.ico" rel="icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- CSS -->
    <link href="assets/vendors/aos/aos.css" rel="stylesheet">
    <link href="assets/vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendors/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendors/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendors/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
    crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
    integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
    crossorigin=""></script>

    <style>
        #map {
			border: 1px gray solid;
			float: right;
			margin: 0 0 20px 20px;
		}

		#mapid {
			width: 100%;
			height: 324px;
		}
    </style>
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
                    <li><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li><a href="penelusuran.php">Penelusuran</a></li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
        </div>
    </header>

    <section id="hero" class="hero d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">Halaman detail rumah sakit</h1>
                    <h2 class="fs-5" data-aos="fade-up" data-aos-delay="400">
                        Anda dapat melihat detail dari data rumah sakit yang telah Anda cari di kolom pencarian.
                    </h2>
                </div>
                <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                    <img src="assets/img/detail_image.svg" class="img-fluid" alt="Detail Image">
                </div>
            </div>
        </div>
    </section>

    <main id="main">
        <section id="about" class="about">
            <div class="container" data-aos="fade-up">
                <div class="row gx-0">
                    <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="content rounded">
                            <h3><?= $detail['name'] ?></h3>
                            <p style="text-align: justify;"><?= $detail['description']; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
                        <img src="<?= $foto_url ?>" class="img-fluid rounded" alt="">
                    </div>
                </div>
                <div class="mt-5">
                    <?php
                    print "<div id='mapid'></div>";
                    $map_script = "var mymap = L.map('mapid').setView([" . $detail['lat'] . ", " . $detail['long'] . "], 13);
                            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
                            maxZoom: 18,
                            attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors, ' +
                                    '<a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' +
                                        'Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',
                            id: 'mapbox/streets-v11',
                            tileSize: 512,
                            zoomOffset: -1
                        }).addTo(mymap);

                        L.marker([" . $detail['lat'] . ", " . $detail['long'] . "]).addTo(mymap)
                        .bindPopup(\"<b>" . $detail['location'] . "</b>\").openPopup();";

                    print "<script>" . $map_script . "</script>";
                    ?>
                </div>
            </div>
        </section>
    </main>

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

    <script src="assets/js/main.js"></script>
</body>

</html>