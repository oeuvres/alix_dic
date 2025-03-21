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

function merge()
{
    $words = [];
    $duplicates = [];
    $handle = @fopen("word.csv", "r");
    if ($handle) {
        while (($line = fgets($handle, 4096)) !== false) {
            if ($line[0] == '#') continue;
            $data = str_getcsv($line, ";");
            // take first one
            $graph = $data[0];
            $cat = $data[1];
            // get first cat
            if (!isset($words[$graph])) {
                $words[$graph] = $cat;
            }
            else {
                $duplicates[$graph] = $words[$graph];
            }
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
    print_r($duplicates);
    return;

    rename("grammalecte.tsv", "_grammalecte.tsv");
    $read = @fopen("_grammalecte.tsv", "r");
    $write = @fopen("grammalecte.tsv", "w");
    if ($read) {
        while (($line = fgets($read, 4096)) !== false) {
            $data = str_getcsv($line, "\t");
            $graph = $data[0];
            $cat = $data[2];
            if (
                !isset($words[$graph])
                || $cat != '?'
            ) {
                fwrite($write, $line);
                continue;
            }
            // a cat to import
            else {
                $data[2] = $words[$graph];
                fwrite($write, implode("\t", $data) . "\n");
            }
        }
        if (!feof($read)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($read);
        fclose($write);
    }
    unlink("_grammalecte.tsv");
}

function toWord()
{
    $read = @fopen(__DIR__ . "/grammalecte.tsv", "r");
    $write = @fopen(dirname(__DIR__) . "/alix/src/main/resources/com/github/oeuvres/alix/fr/word.csv", "w");
    if ($read) {
        while (($line = fgets($read, 4096)) !== false) {
            $data = str_getcsv($line, "\t");
            if ($data[0] == $data[1]) $data[1] = "";
            $data = array_slice($data, 0, 3);
            fwrite($write, implode(",", $data) . "\n");
        }
        if (!feof($read)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($read);
        fclose($write);
    }
}

function ppas()
{
    $write = "";
    $read = @fopen(__DIR__ . "/grammalecte.tsv", "r");
    while (($line = fgets($read, 4096)) !== false) {
        $data = str_getcsv($line, "\t");
        if (!isset($data[3]) || strpos($data[3], "ppas adj") === FALSE) {
            $write .= $line;
            continue;
        }
        else if ($data[1] != "ADJ") {
            $write .= $line;
            continue;
        }
        else {
            $write .= $data[0] . "\tADJ\t" . preg_replace("/e?s?$/", "", $data[0]) . "\t" . $data[3] . "\n";
            $write .= $data[0] . "\tVERB\t" . $data[2] . "\t" . $data[3] . "\n";
        }
    }
    if (!feof($read)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($read);
    file_put_contents(__DIR__ . "/grammalecte.tsv", $write);
}

toWord();
