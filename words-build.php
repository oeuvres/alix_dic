<?php
/**
 # Alix, lexique pour lemmatisation

* Convertir les étiquettes de grammalecte au format Alix
* ? Pas de lemme dans la colonne lemme si c’est la ligne du lemme
* Trier par ordre alphabétique, puis par étiquette
* Trier les homographes comme word.csv pour les étiquettes
* Lister les homographes inconnu

*/


function prenoms()
{
    $forename = [];
    $handle = @fopen("forename.csv", "r");
    if ($handle) {
        while (($line = fgets($handle, 4096)) !== false) {
            if ($line[0] == '#') continue;
            $data = str_getcsv($line, ",");
            $forename[$data[0]] = $data[1];
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
    $handle = @fopen("grammalecte.tsv", "r");
    if ($handle) {
        while (($line = fgets($handle, 4096)) !== false) {
            if (strpos($line, "\tprn") === false) continue;
            $data = str_getcsv($line, "\t");
            $name = $data[0];
            if (isset($forename[$name])) continue;
            $cat = "NAMEpers";
            if (strpos($line, "\tprn mas") !== false) $cat = "NAMEpersm";
            else if (strpos($line, "\tprn fem") !== false) $cat = "NAMEpersf";
            echo "$name,$cat\n";
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
}

