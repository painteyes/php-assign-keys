<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ParkinGo</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id='container'>

            <?php
                $page_1 = ($_GET['page_1'] ? $_GET['page_1'] : 1);  
                $page_2 = ($_GET['page_2'] ? $_GET['page_2'] : 1);
                $page_3 = ($_GET['page_3'] ? $_GET['page_3'] : 1);
                
                if(!empty($_POST['id'])) {
                    $id = $_POST['id'];
                    $location = "Location: /php-assign-keys/?id=$id&page_1=$page_1&page_2=$page_2&page_3=$page_3";
                    header($location);
                }

                if(isset($_GET['id'])){
                    require_once 'assignKey.php';
                    $id = $_GET['id'];
                    $result = assignKey($id);
                }
            ?>

            <!-- Logo -->
            <div class='logo d-flex justify-content-center p-4'>
                <img class='w-25 p-3' src="https://www.parkingo.com/images/parkingo.svg" alt="logo">
            </div> 

            <!-- Script -->
            <?php

                // Select the rows to display
                require(__DIR__.'/db/connection.php');

                $sql_1 = 'SELECT * FROM prk_prntz AS P WHERE P.accettata = 0';
                $sql_2 = 'SELECT * FROM prk_prntz AS P INNER JOIN prk_accettazioni AS A ON P.id = A.IDprk_prntz WHERE P.accettata = 1';
                $sql_3 = 'SELECT * FROM prk_prntz AS P INNER JOIN prk_accettazioni AS A ON P.id = A.IDprk_prntz WHERE P.accettata = 2';

                $result_1 = $connection->query($sql_1);
                $result_2 = $connection->query($sql_2);
                $result_3 = $connection->query($sql_3);

                // PAGINATION

                // Finds out the number of results stored in databse
                $number_of_results_1 = $result_1->rowCount();
                $number_of_results_2 = $result_2->rowCount();
                $number_of_results_3 = $result_3->rowCount();

                // Defines how many results per page
                $result_per_page = 7;

                // Determines number of total pages available
                $number_of_pages_1 = ceil($number_of_results_1 / $result_per_page);
                $number_of_pages_2 = ceil($number_of_results_2 / $result_per_page);
                $number_of_pages_3 = ceil($number_of_results_3 / $result_per_page);

                // Determines the number of page links
                $links = 2;

                // Determines which number is currently on
                if(!isset($_GET['page_1'])) {
                    $page_1 = 1;
                } else {
                    $page_1 = $_GET['page_1'];
                }
                if(!isset($_GET['page_2'])) {
                    $page_2 = 1;
                } else {
                    $page_2 = $_GET['page_2'];
                }
                if(!isset($_GET['page_3'])) {
                    $page_3 = 1;
                } else {
                    $page_3 = $_GET['page_3'];
                }

                // Determines the sql LIMIT starting number for the results on the current page
                $starting_limit_number_1 = ($page_1 - 1) * $result_per_page ;
                $starting_limit_number_2 = ($page_2 - 1) * $result_per_page ;
                $starting_limit_number_3 = ($page_3 - 1) * $result_per_page ;

                // Determines the start and end numbers of pages links 
                if ($page_1 - $links > 0) {
                    $start_1 = $page_1 - $links;
                    $end_1 = $page_1 + $links;
                } else {
                    $start_1 = 1;
                    $end_1 = ($links * 2) + 1;
                }

                if ($page_2 - $links > 0) {
                    $start_2 = $page_2 - $links;
                    $end_2 = $page_2 + $links;
                } else {
                    $start_2 = 1;
                    $end_2 = ($links * 2) + 1;
                }
                
                if ($page_3 - $links > 0) {
                    $start_3 = $page_3 - $links;
                    $end_3 = $page_3 + $links;
                } else {
                    $start_3 = 1;
                    $end_3 = ($links * 2) + 1;
                }

                // Limits the results per page
                $sql_1 .= ' LIMIT ' . $starting_limit_number_1 . ',' . $result_per_page;
                $sql_2 .= ' LIMIT ' . $starting_limit_number_2 . ',' . $result_per_page;
                $sql_3 .= ' LIMIT ' . $starting_limit_number_3 . ',' . $result_per_page;

                // Runs queries
                $result_1 = $connection->query($sql_1);
                $result_2 = $connection->query($sql_2);
                $result_3 = $connection->query($sql_3);

            ?>

            <div class='row'>

                <!-- Reservation view -->
                <section class='col'>

                    <!-- Title -->
                    <h3>Prenotazioni</h3>

                    <!-- Table -->
                    <table class="table table-light table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Prenotazione</th>
                                <th>Arrivo</th>
                                <th>Targa</th>
                                <th>Volo di rientro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while($row_1 = $result_1->fetch()) {
                            echo 
                            '<tr>
                                <td>' . $row_1['id'] . '</td>
                                <td>' . date('Y-m-d', strtotime($row_1['arrivo'])) . '</td>
                                <td>' . $row_1['targa'] . '</td>
                                <td>' . $row_1['volo_rientro'] . '</td>
                            </tr>'; 
                            } 
                            echo 
                        '</tbody>
                    </table>';

                    // Page links
                    echo
                    '<ul class="pagination d-flex justify-content-end">
                        <li class="page-item">
                            <a class="page-link" href="?page_1=' . ($page_1 > 1 ? $page_1 - 1 : $page_1) 
                            . '&page_2=' . ($page_2)
                            . '&page_3=' . ($page_3)
                            . '">Indietro</a>
                        </li>';
                        for($i = $start_1; $i < $end_1; $i++) {
                            echo 
                            '<li class="page-item' . ($page_1 == $i ? ' active' : '') . '">
                                <a class="page-link" href="?page_1=' . $i 
                                . '&page_2=' . ($page_2)
                                . '&page_3=' . ($page_3)
                                . '">' . $i . '</a>
                            </li>';
                        }
                        echo
                        '<li class="page-item">
                            <a class="page-link" href="?page_1=' . ($page_1 < $number_of_pages_1 ? $page_1 + 1 : $page_1) 
                            . '&page_2=' . ($page_2)  
                            . '&page_3=' . ($page_3)
                            . '">Avanti</a>
                        </li>
                    </ul>';
                    ?>
                </section>

                <!-- Cars in storage view -->
                <section class='col'>

                    <!-- Title -->
                    <h3>Auto in giacenza</h3>

                    <!-- Table -->
                    <table class="table table-light table-striped table-bordered">

                        <thead>
                            <tr>
                                <th>Prenotazione</th>
                                <th>Arrivo</th>
                                <th>Targa</th>
                                <th>Volo di rientro</th>
                                <th>Chiave</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while($row_2 = $result_2->fetch()) {

                            echo 
                            '<tr>
                                <td>' . $row_2['IDprk_prntz'] . '</td>
                                <td>' . date('Y-m-d', strtotime($row_2['arrivo'])) . '</td>
                                <td>' . $row_2['targa'] . '</td>
                                <td>' . $row_2['volo_rientro'] . '</td>
                                <td>' . $row_2['n_chiave'] . '</td>
                            </tr>'; 
                            } 
                            echo 
                            '</tbody>
                            </table>';

                            // Page links
                            echo 
                            '<ul class="pagination d-flex justify-content-end">
                            <li class="page-item">
                                <a class="page-link" href="?page_1=' . ($page_1)
                                . '&page_2=' . ($page_2 > 1 ? $page_2 - 1 : $page_2) 
                                . '&page_3=' . ($page_3)
                                . '">Indietro</a>
                            </li>';

                            for($i = $start_2; $i < $end_2; $i++) {
                                echo 
                                '<li class="page-item' . ($page_2 == $i ? ' active' : '') . '">
                                    <a class="page-link" href="?page_1=' . ($page_1)
                                    . '&page_2=' . $i 
                                    . '&page_3=' . ($page_3)
                                    . '">' . $i . '</a>
                                </li>';
                            }

                            echo
                            '<li class="page-item">
                                <a class="page-link" href="?page_1=' . ($page_1)
                                . '&page_2=' . ($page_2 < $number_of_pages_2 ? $page_2 + 1 : $page_2) 
                                . '&page_3=' . ($page_3)
                                . '">Avanti</a>
                            </li>';
                            echo 
                            '</ul>';
                        ?>     
                </section> 
            </div>

            <div class='row'>

                <!-- Historical view -->
                <section class='col'>

                    <!-- Title -->
                    <h3>Storico</h3>

                    <!-- Table -->
                    <table class="table table-light table-striped table-bordered">

                        <thead>
                            <tr>
                                <th>Prenotazione</th>
                                <th>Arrivo</th>
                                <th>Targa</th>
                                <th>Volo di rientro</th>
                                <th>Chiave</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php 
                            while($row_3 = $result_3->fetch()) {
                            echo 
                            '<tr>
                                <td>' . $row_3['IDprk_prntz'] . '</td>
                                <td>' . date('Y-m-d', strtotime($row_3['arrivo'])) . '</td>
                                <td>' . $row_3['targa'] . '</td>
                                <td>' . $row_3['volo_rientro'] . '</td>
                                <td>' . $row_2['n_chiave'] . '</td>
                            </tr>'; 
                            } 
                            echo 
                            '</tbody>
                            </table>';

                            // Page links
                            echo
                            '<ul class="pagination d-flex justify-content-end">
                                <li class="page-item">
                                    <a class="page-link" href="?page_1=' . ($page_1) 
                                    . '&page_2=' . ($page_2)
                                    . '&page_3=' . ($page_3 > 1 ? $page_3 - 1 : $page_3) 
                                    . '">Indietro</a>
                                </li>';
                                for($i = $start_3; $i < $end_3; $i++) {
                                    echo 
                                    '<li class="page-item' . ($page_3 == $i ? ' active' : '') . '">
                                        <a class="page-link" href="?page_1=' . ($page_1)
                                        . '&page_2=' . ($page_2)
                                        . '&page_3=' . $i  
                                        . '">' . $i . '</a>
                                    </li>';
                                }
                                echo
                                '<li class="page-item">
                                    <a class="page-link" href="?page_1=' . ($page_1)
                                    . '&page_2=' . ($page_2)
                                    . '&page_3=' . ($page_3 < $number_of_pages_3 ? $page_2 + 1 : $page_2)  
                                    . '">Avanti</a>
                                </li>
                            </ul>';
                        ?>      
                </section>

                <!-- Assign key functionality -->
                <section class='col key'>

                    <!-- Title -->
                    <h3>Chiave</h3>

                    <!-- Form -->
                    <?php echo '<form method="post" action="/php-assign-keys/?page_1='.$page_1.'&page_2='.$page_2.'&page_3='.$page_3.'">'; ?>
                        <label for="id" class="form-label">Inserisci il numero di prenotazione</label>
                        <input name="id" type="number" class="form-control" id="id" placeholder='Esempio: 1869930'>
                        <input type="submit" class="btn btn-primary" value='Assegna chiave'>
                    </form>

                    <?php 
                        if (isset($result)) {
                            $key = $result['key'];
                            if($key) {
                                $message = $result['message'];
                                echo "<div class='alert alert-success' role='alert'>$message</div>";
                            } else {
                                $message = $result['message'];
                                echo "<div class='alert alert-danger' role='alert'>$message</div>";
                            }
                        }
                    ?>

                </section>

            </div>
        </div>
    </body>
</html>