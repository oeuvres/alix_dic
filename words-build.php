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

function words()
{
    $words = [];
    $handle = @fopen("word.csv", "r");
    if ($handle) {
        while (($line = fgets($handle, 4096)) !== false) {
            if ($line[0] == '#') continue;
            $data = str_getcsv($line, ";");
            // take first one
            $graph = $data[0];
            if (isset($words[$graph])) continue;
            $words[$graph] = $data[1];
            /*
            if ($data[3] != 1) continue;
            $lem = $data[0];
            $cat = $data[1];
            if ($cat == 'VERBppas' || $cat == 'VERBger') {
                continue;
            }

            if (isset($lemmata[$lem])) {
                if ($lemmata[$lem] == $cat) continue;
                $lemmata[$lem] = false;
                continue;
            }
            else {
                $lemmata[$lem] = $cat;
            }
            */
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
    rename("grammalecte.tsv", "_grammalecte.tsv");
    $read = @fopen("_grammalecte.tsv", "r");
    $write = @fopen("grammalecte.tsv", "w");
    if ($read) {
        while (($line = fgets($read, 4096)) !== false) {
            $data = str_getcsv($line, "\t");
            $lem = $data[1];
            $cat = $data[2];
            if (
                !isset($lemmata[$lem])
                || !$lemmata[$lem]
                || $cat == 'VERBppas'
                || $cat == 'VERBger'
            ) {
                fwrite($write, $line);
                continue;
            }
            $data[2] = $lemmata[$lem];
            fwrite($write, implode("\t", $data) . "\n");
        }
        if (!feof($read)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($read);
        fclose($write);
    }
    unlink("_grammalecte.tsv");
}

words();