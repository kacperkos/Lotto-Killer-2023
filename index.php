<?php

require 'vendor/autoload.php';

use Lottokiller\Game\AllCombinations;
use Lottokiller\Game\PastLotteries;
use Lottokiller\Rules\SumOfPastElements;
use Lottokiller\Rules\OmitPastLotteries;
use Lottokiller\Rules\OmitInrowElements;
use Lottokiller\Rules\OmitEvenOddElements;
use Lottokiller\Selectors\LuckOfTheIrish;

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
//$all_combinations = AllCombinations::getInstance();
//$all_combinations = AllCombinations::getInstance([2,4,6,8,10,12,14], 4);
$all_combinations = AllCombinations::getInstance('loadFromCache');

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

//$all_combinations->cacheCombinations();

/*
//Liczby z kuponów
//16.08.23
$all_combinations->ifExistsByNumbers([12,18,19,26,35], true);
$all_combinations->ifExistsByNumbers([9,12,28,34,36], true);
$all_combinations->ifExistsByNumbers([1,7,10,20,36], true);
$all_combinations->ifExistsByNumbers([7,14,21,23,33], true);
//06.07.23
$all_combinations->ifExistsByNumbers([5,23,30,36,41], true);
$all_combinations->ifExistsByNumbers([3,6,12,27,35], true);
$all_combinations->ifExistsByNumbers([10,12,16,32,41], true);
$all_combinations->ifExistsByNumbers([2,8,29,30,33], true);
//27.06.23
$all_combinations->ifExistsByNumbers([13,14,15,20,37], true);
$all_combinations->ifExistsByNumbers([6,18,30,32,38], true);
$all_combinations->ifExistsByNumbers([14,23,29,33,41], true);
$all_combinations->ifExistsByNumbers([7,18,38,40,42], true);
*/

$past_lotteries = new PastLotteries();

$selector_1 = new LuckOfTheIrish($all_combinations, $past_lotteries);
$selector_1->run();

unset($all_combinations);
unset($past_combinations);
?>
    </body>
</html>