<?php
require_once realpath(__DIR__ . '') . "/vendor/autoload.php";
require_once __DIR__ . "/html_tag_helpers.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Tugas Besar Web Semantik - Pencarian Rumah Sakit" name="description">

    <title>Rumah Sakit</title>
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

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
    <?php
    $uri_rdf = 'http://localhost/tubes_hospital/hospital.rdf';
    $data = \EasyRdf\Graph::newAndLoad($uri_rdf);
    $doc = $data->primaryTopic();

    // Ambil data dari rdf
    $PCHospital_uri = '';
    foreach ($doc->all('owl:sameAs') as $akun) {
        $PCHospital_uri = $akun->get('foaf:homepage');
        break;
    }

    // Inisialisasi namespace
    \EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    \EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
    \EasyRdf\RdfNamespace::set('dbr', 'http://dbpedia.org/resource/');
    \EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');
    \EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
    \EasyRdf\RdfNamespace::set('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
    \EasyRdf\RdfNamespace::set('foaf', 'http://xmlns.com/foaf/0.1/');

    // Set SPARQL endpoint
    $sparql_endpoint = 'https://dbpedia.org/sparql';
    $sparql = new \EasyRdf\Sparql\Client($sparql_endpoint);

    $sparql_query = '
    SELECT distinct * WHERE {
        <' . $PCHospital_uri . '> dbo:region ?kota;
            rdfs:comment ?info;
            dbo:abstract ?description;
            foaf:isPrimaryTopicOf ?wiki;
            rdfs:label ?kota_label;
            geo:lat ?lat;
            geo:long ?long.
       FILTER (lang(?info) = "en" && lang(?kota_label) )
   }
   ';

    $result = $sparql->query($sparql_query);

    // Ambil detail rumah sakit dari $result SPARQL
    $detail = [];
    foreach ($result as $row) {
        $detail = [
            'kota' => $row->kota_label,
            'info' => $row->info,
            'description' => $row->description,
            'lat' => $row->lat,
            'long' => $row->long,
            'wiki' => $row->wiki,
        ];
        break;
    }
    ?>
    <header id="header" class="header fixed-top">
        <div class="container container-xl d-flex align-items-center justify-content-between">
            <a href="#hero" class="logo d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36">
                    <path fill="none" d="M0 0h24v24H0z" />
                    <path d="M8 20v-6h8v6h3V4H5v16h3zm2 0h4v-4h-4v4zm11 0h2v2H1v-2h2V3a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v17zM11 8V6h2v2h2v2h-2v2h-2v-2H9V8h2z" fill="rgba(1,41,112,1)" />
                </svg>
                <span class="ps-2">Hospital</span>
            </a>
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link active" href="#hero">Beranda</a></li>
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
                    <h1 data-aos="fade-up">Selamat datang di website kami!</h1>
                    <h2 class="fs-5" data-aos="fade-up" data-aos-delay="400">
                        Kami dari kelompok 1 Kom-C membuat website yang bisa mencari data rumah sakit di seluruh dunia.
                    </h2>
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="text-center text-lg-start">
                            <a href="penelusuran.php" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Cari Data</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                    <img src="assets/img/hero_image.svg" class="img-fluid" alt="Hero Image">
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
                            <h3><?= $doc->get('foaf:name') ?></h3>
                            <p style="text-align: justify;"><?= $detail['description']; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
                        <?php
                        \EasyRdf\RdfNamespace::setDefault('og');

                        $wiki = \EasyRdf\Graph::newAndLoad($detail['wiki']);
                        $foto_url = $wiki->image;
                        ?>
                        <img src="<?= $foto_url ?>" class="img-fluid rounded" alt="">
                    </div>
                </div>
                <div class="mt-5" data-aos="zoom-out" data-aos-delay="200">
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
                        .bindPopup(\"<b>" . $detail['kota'] . "</b>\").openPopup();";

                    print "<script>" . $map_script . "</script>";
                    ?>
                </div>
            </div>
        </section>

        <section id="team" class="team">
            <div class="container" data-aos="fade-up">
                <header class="section-header">
                    <h2>Kelompok</h2>
                    <p>Kelompok Kami</p>
                </header>
                <div class="row gy-4">
                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-1.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota1') ?></h4>
                                <span><?= $doc->get('foaf:nim1') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-2.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota2') ?></h4>
                                <span><?= $doc->get('foaf:nim2') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-3.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota3') ?></h4>
                                <span><?= $doc->get('foaf:nim3') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-4.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota4') ?></h4>
                                <span><?= $doc->get('foaf:nim4') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-4.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota5') ?></h4>
                                <span><?= $doc->get('foaf:nim5') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
                        <div class="member">
                            <div class="member-img">
                                <img src="assets/img/team/team-4.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="member-info">
                                <h4><?= $doc->get('foaf:anggota6') ?></h4>
                                <span><?= $doc->get('foaf:nim6') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer id="footer" class="footer p-0">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-5 col-md-12 footer-info">
                        <a href="#hero" class="logo d-flex align-items-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path d="M8 20v-6h8v6h3V4H5v16h3zm2 0h4v-4h-4v4zm11 0h2v2H1v-2h2V3a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v17zM11 8V6h2v2h2v2h-2v2h-2v-2H9V8h2z" fill="rgba(1,41,112,1)" />
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