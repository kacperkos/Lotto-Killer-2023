<?php

require 'vendor/autoload.php';

use Lottokiller\Game\AllCombinations;
//use Lottokiller\Game\PastLotteries;
use Lottokiller\Rules\SumOfPastElements;
use Lottokiller\Rules\OmitPastLotteries;
use Lottokiller\Rules\OmitInrowElements;
use Lottokiller\Rules\OmitEvenOddElements;

?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <title>Lotto-Killer</title>
    </head>
    <body>
<?php

//Sposoby inicjowania zbioru wszystkich możliwych kombinacji
//Obiekt AllCombinations to Singleton!
$all_combinations = AllCombinations::getInstance();
//$all_combinations = AllCombinations::getInstance('abc.txt');
//$all_combinations = AllCombinations::getInstance([2,4,6,8,10,12,14], 4);

//Wyświetlanie aktualnego stanu obiektu AllCombinations
//$all_combinations->dump();

//Header
echo '<p>Lotto-Killer | version: pre-alpha</p>';
echo '<p>GRA: ' . $all_combinations->getK() . ' z ' . $all_combinations->getCountOfNumbers() . ' liczb</p>';

//Wyświetlanie aktualnego prawdopodobieństwa na wygraną
echo $all_combinations->getCurrentChances();

/*
//REGUŁA #1 (+objaśnienia)
//Sposób usuwania z obiektu AllCombinations kombinacji według przyjętej reguły
//Tworzenie obiektu reguły
$rule_1 = new SumOfPastElements();
//Konfiguracja paremetrów
$rule_1->setStep(5);
$rule_1->setImportance(2);
//Sposób działania reguły można najpierw zwizualizować!
$rule_1->visualize();
//$all_combinations->removeCombinationsByRule($rule_1);
//Wywołanie poprzez echo spowoduje wyświetlenie komunikatu zwrotnego
echo $all_combinations->removeCombinationsByRule($rule_1);
unset($rule_1);
echo $all_combinations->getCurrentChances();
*/

/*
//REGUŁA #2
$rule_2 = new OmitPastLotteries();
$rule_2->visualize();
echo $all_combinations->removeCombinationsByRule($rule_2);
unset($rule_2);
echo $all_combinations->getCurrentChances();
*/

/*
//REGUŁA #3
$rule_3 = new OmitInrowElements();
$rule_3->setMinimumN(2);
$rule_3->setImportance(4);
$rule_3->visualize();
echo $all_combinations->removeCombinationsByRule($rule_3);
unset($rule_3);
echo $all_combinations->getCurrentChances();
*/

/*
//REGUŁA #4
$rule_4 = new OmitEvenOddElements();
$rule_4->setImportance(3);
$rule_4->visualize();
echo $all_combinations->removeCombinationsByRule($rule_4);
unset($rule_3);
echo $all_combinations->getCurrentChances();
*/

//$all_combinations->ifExistsByNumbers([1,2,3,4,5], true);

unset($all_combinations);
?>
    </body>
</html>