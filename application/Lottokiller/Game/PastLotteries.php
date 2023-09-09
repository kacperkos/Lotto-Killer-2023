<?php

namespace Lottokiller\Game;

class PastLotteries
{
    const CSV_FILE_NAME = 'minilotto6340.csv';
    private $past_lotteries = [];
    private $k;

    public function __construct()
    {
        $file_string = file_get_contents('input/' . self::CSV_FILE_NAME);
        if ($file_string !== false) {
            $file_string_exploded = explode("\n", $file_string);
            $past_lotteries_index = 0;
            foreach ($file_string_exploded as $index => $row) {
                if(!empty($row)) {
                    $row_exploded = explode(",", $row);
                    $numbers_exploded = explode(" ", $row_exploded[2]);
                    //$row_exploded[0] -> //numer losowania
                    //$row_exploded[1] -> //data losowania
                    foreach ($numbers_exploded as $index => &$number) {
                        $number = intval($number);
                    }
                    $this->past_lotteries[$past_lotteries_index] = $numbers_exploded;
                    if($past_lotteries_index === 0) {
                        $this->k = count($this->past_lotteries[$past_lotteries_index]);
                    }
                    $this->past_lotteries[$past_lotteries_index]['lottery_id'] = intval($row_exploded[0]);
                    $this->past_lotteries[$past_lotteries_index]['lottery_date'] = $row_exploded[1];
                }
                $past_lotteries_index++;
            }
        } elseif ($file_string === false) {
            echo 'Nie znaleziono pliku ' . self::CSV_FILE_NAME . "<br />PROGRAM ZATRZYMANO!";
            exit();
        }
    }
    public function dump()
    {
        var_dump($this->past_lotteries);
    }
    //
    // METODY 'IF'
    //
    //
    // METODY 'GET'
    //
    public function getLotteryById($id)
    {
        //Metoda zwraca przeszłą loterię o zadanym ID loterii
        foreach ($this->past_lotteries as $lottery) {
            if ($lottery['lottery_id'] === $id) {
                return $lottery;
            }
        }
    }
    public function getLotteryByIndex($index)
    {
        //Metoda zwraca przeszłą loterię o zadanym index'ie
        if (isset($this->past_lotteries[$index])) {
            return $this->past_lotteries[$index];
        }
    }
    public function getAllLotteries()
    {
        //Metoda zwraca wszystkie przeszłe loterie (poza tymi już usuniętymi
        //po zastosowaniu konkretnych reguł)
        return $this->past_lotteries;
    }
    public function getK()
    {
        //Metoda zwraca ilość liczb w pojedyńczym przeszłym losowaniu
        return $this->k;
    }
    //
    // METODY 'ADD'
    //
    public function addNewColumnToRow($row_index, $column_name, $column_value)
    {
        //Metoda dodaje nową kolumnę do każdego wiersza w tablicy
        //przechowującej przeszłe losowania
        $this->past_lotteries[$row_index][$column_name] = $column_value;
    }
    //
    // METODY 'REMOVE'
    //
    public function removeLotteryByIndex($index)
    {
        //Metoda usuwa losowanie/wiersz o zadanym index'ie
        unset($this->past_lotteries[$index]);
    }
    public function removeColumnGlobally(string $column_name)
    {
        //Metoda usuwa kolumnę z tablicy przechowującej przeszłe losowania
        foreach ($this->past_lotteries as $index => $lottery) {
            if (isset($lottery[$column_name])) {
                unset($this->past_lotteries[$index][$column_name]);
            }
        }
    }
}