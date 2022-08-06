<?php

    // Takes the P.id number as argument, sets the field A.n_chiave and returns the value set
    function assignKey($id) {

        // Prevents sql injections
        if(!is_numeric($id)){
            return ['key' => null, 'message' => "È necessario inserire un numero."];
        }

        // Connects to the database
        require_once(__DIR__.'/db/connection.php');
        
        // Selects the row to be updated with the column values to be checked to determine which value to set on A.n_key  
        $sql_1 = "SELECT P.id, A.n_chiave, P.accettata, P.IDparks, P.arrivo
        FROM prk_accettazioni AS A 
        INNER JOIN prk_prntz AS P ON P.id = A.IDprk_prntz
        WHERE P.id = $id
        AND P.accettata = 1";
        
        $result_1 = $connection->query($sql_1);

        if($result_1 == false) {
            return ['key' => null, 'message' => "Non è stato possibile eseguire la query ($sql_1)."];
        }
        
        // Check that the result contains a row otherwise stops the function and show a warning message
        if($result_1->rowCount() == 0) {
            return ['key' => null, 'message' => "Il numero di prenotazione non è valido o la prenotazione non è stata accettata."];
        } 
        
        // Gets the row as array    
        $row_1 = $result_1->fetch();
        
        // Check if the value of A.n_key is already valued, if it is, stops the function and return it
        if($row_1['n_chiave'] != 0) {
            $key = $row_1['n_chiave'];
            return ['key' => null, 'message' => "Il numero chiave è già stato assegnato a questa prenotazione (numero chiave = $key)."]; 
        } 
        
        // Saves the IDparks value of the row to be updated 
        $row_IDparks = $row_1['IDparks'];

        // Declares and initializes them to 0 the minimum and maximum values in the range of valid values; then reassigns them according to the IDparks
        $min_range = 0;
        $max_range = 0;
        $second_min_range = 0;
        $second_max_range = 0;

        if ($row_IDparks == 1) {
            $min_range = 1041;
            $max_range = 1346;
        } 
        elseif ($row_IDparks == 2) {
            $min_range = 1;
            $max_range = 1040;
            $second_min_range = 1381;
            $second_max_range = 1721;
        }
             
        /* 
        Gets the key numbers that have already been assigned from 1 up to 3 times within the established range based on IDparks, 
        that have P.arrivo different from that of the reservation. Then sorts the results in ascending order based on the number of times each key has been assigned.
        */
        $date = date('Y-m-d', strtotime($row_1['arrivo']));

        $sql_2 = "SELECT A.n_chiave, COUNT(*) AS 'count'
        FROM prk_accettazioni AS A
        INNER JOIN prk_prntz AS P ON P.id = A.IDprk_prntz
        WHERE P.arrivo != $date
        AND (
            CASE
                WHEN $row_IDparks = 1 THEN A.n_chiave BETWEEN $min_range AND $max_range
                WHEN $row_IDparks = 2 THEN A.n_chiave BETWEEN $min_range AND $max_range OR A.n_chiave BETWEEN $second_min_range AND $second_max_range
            END
        )
        GROUP BY A.n_chiave
        HAVING COUNT(*) <= 3
        ORDER BY COUNT(*) ASC";

        $result_2 = $connection->query($sql_2);

        if($result_2 == false) {
            return ['key' => null, 'message' => "Non è stato possibile eseguire la query ($sql_2)."];
        }

        // Declares and initialize to 0 the key to be assigned 
        $key = 0;

        // Checks if the result contains rows, if not, any keys in the range of valid ones has still been assigned from 1 to 3 times, so assigns the minimum value
        if($result_2->rowCount() == 0) { 
            $key = $min_range;
        } 
        else {
            
            // Declares and initializes empty an array where to save keys that can still be assigned (so assigned maximum up to 2 times)
            $array_keys_assignable = [];

            /* Declares and initializes empty an array where to save both 
            the keys that can still be assigned (so assigned maximum up to 2 times) 
            and the keys that can no longer be assigned (therefore assigned up to 3 times)
            */
            $array_keys = [];

            while($row_2 = $result_2->fetch()) {
                // Save only assignable keys in $array_keys_assignable
                if($row_2['count'] < 3) {
                    $array_keys_assignable[] = $row_2['n_chiave'];
                }
                // Saves all keys in $array_keys
                if ($row_2['count'] <= 3) {
                    $array_keys[] = $row_2['n_chiave'];
                }
            } 

            // Checks if $array_keys_assignable contains keys, if not, all keys have been assigned 3 times, so stops the function and show a warning message
            if(count($array_keys_assignable) == 0) {
                return ['key' => null, 'message' => 'I numeri chiave sono esauriti'] ;
            }

            // Declares and initializes empty an array in which to store all valid values that can be assigned as a key; then reassigns it according to IDparks
            $range = [];
            
            if ($row_IDparks == 1) {
                $range = range($min_range, $max_range);
            } 
            if ($row_IDparks == 2) {
                $range = array_merge(range($min_range, $max_range), range($second_min_range, $second_max_range));
            }
            
            /*
            Runs the difference between the range of valid values and the keys already assigned.
            Gets a resulting array with any values not yet assigned as a key OR an empty array if all keys have already been assigned at least once.
            */
            $array_diff = array_values(array_diff($range, $array_keys));
           
            /*
            If $array_diff is empty, the first key within $array_keys_assignable is reassigned, as the keys within it come from $result_2, 
            which already sorts the keys in ascending order based on the number of times they were assigned
            */
            if (count($array_diff) == 0) {
                $key = $array_keys_assignable[0]; 
            } 
            /*  
            Otherwise there are numbers not yet assigned, so assigns the first one (the values inside $array_diff are in ascending order)
            */
            else {
                $key = $array_diff[0]; 
            }
            
        } 

        // Converts $key to a string for consistency with the data type in the database
        $key = strval($key);

        // Set the A.n_key field corresponding to the P.id received as a parameter
        $sql_3 = "UPDATE prk_accettazioni
        SET n_chiave = $key
        WHERE IDprk_prntz = $id";

        $result_3 = $connection->query($sql_3);

        if ($result_3) {
            return ['key' => $key, 'message' => "Il numero chiave è stato assegnato correttamente (numero chiave: $key)."];
        } else {
            return ['key' => null, 'message' => "Non è stato possibile eseguire la query ($sql_3)."];
        }

    }

?>



