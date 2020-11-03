<?php
//VARIABLES-------------------------------------------------------------------------------------------------------------

require_once 'db_config.php';

$enteredName = htmlspecialchars($_POST ['name'] == '' ? 'anonymous' : $_POST ['name']); // имя введенное пользователем
$requestSum = htmlspecialchars($_POST ['sum']); // сумма введенная пользователем
$date = date('Y-m-d H:i:s');

$residueSum = $requestSum; // разница между запрошенной пользователем суммой и начисленной для выдачи в ходе выполнения программы
$getSum = null; // сумма денег для выдачи пользователю (начисляется в ходе выполнения программы)

try {
    $query = "SELECT `denomination`, `number_banknotes` FROM `available_banknotes`"; // запрос к БД для получения инф. о доступных банкнотах
    $result = mysqli_query($dbConnection, $query);
    $dataArr = mysqli_fetch_all($result, MYSQLI_ASSOC); // массив с информацией о доступных банкнотах
} catch (Exception $e) {
    $dataArr = [];
}

$query = "SELECT SUM(`denomination` * `number_banknotes`) FROM `available_banknotes`"; // запрос к БД для получения суммы доступной в банкомате (до снятия)
$result = mysqli_query($dbConnection, $query);
$dataRow = mysqli_fetch_row($result); // получение строки результирующей таблицы в виде массива
$availSumBefore = $dataRow [0]; // сумма доступная в банкомате (до снятия)
$availSumAfter = $availSumBefore; // сумма доступная в банкомате (после снятия)

$incorrectName = validateName ($enteredName); // строка с описанием ошибки при некорректном вводе имени
$incorrectSum = validateSum ($requestSum); // строка с описанием ошибки при некорректном вводе суммы
$errValidForm = null; // строка с описанием ошибки при некорректном вводе имени или суммы

if ($incorrectName) {
    $errValidForm = $incorrectName;
} elseif ($incorrectSum) {
    $errValidForm = $incorrectSum;
}

$noSum = verifySum ($requestSum, $availSumBefore); // строка с описанием ошибки при отсутствии требуемой суммы в банкомате
$noBanknotes = searchBanknotes ($dataArr, $residueSum); // строка с описанием ошибки при отсутствии необходимых банкнот
$errVerification = null; // строка с описанием ошибки при отсутствии требуемой суммы или банкнот в банкомате

if ($noSum) {
    $errVerification = $noSum;
} elseif ($noBanknotes) {
    $errVerification = $noBanknotes;
}

$remark = null; // сообщение об ошибке (для БД)
$totalUsedBanknotes = []; // массив с кол-вом использованных банкнот каждого номинала

//EXECUTION-------------------------------------------------------------------------------------------------------------

if ($errValidForm) { // проверяем корректность ввода суммы пользователем
    $remark = $errValidForm;
    echo "<div><p>$errValidForm</p></div>";
    echo "<div><p>Доступно $availSumBefore грн</p></div>";
    buttonBack ();
} elseif ($errVerification) { // проверяем наличие требуемой суммы или банкнот в банкомате
    $remark = $errVerification;
    echo "<div><p>$errVerification</p></div>";
    echo "<div><p>Доступно $availSumBefore грн</p></div>";
    buttonBack();
} else {
    for ($i = 0; $i < count($dataArr); $i++) { // перебираем массив с инф. о доступных банкнотах
        $denomination = $dataArr [$i] ['denomination']; // номинал купюр
        $banknotes = $dataArr [$i] ['number_banknotes']; // кол-во доступных банкнот каждого нминала
        $usedBanknotes = null; //кол-во использованных банкнот
        while ($banknotes && $residueSum >= $denomination) {
            $getSum += $denomination;
            $residueSum -= $denomination;
            $banknotes--; // снимаем деньги
            $query = "UPDATE `available_banknotes` SET `number_banknotes` = '{$banknotes}' WHERE `denomination` = '{$denomination}'";
            $result = mysqli_query($dbConnection, $query);
            $dataArr [$i] ['number_banknotes'] = $banknotes;
            $usedBanknotes++;
        }
        if ($usedBanknotes) $totalUsedBanknotes [] = "$usedBanknotes шт. x $denomination грн"; // добавляем количество использованных банкнот в массив
    }
    $numberBanknotes = implode(', ', $totalUsedBanknotes); // объединяем элементы массива с кол-вом использ. банкнот в строку

    $query = "SELECT SUM(`denomination` * `number_banknotes`) FROM `available_banknotes`"; // запрос к БД для получения суммы доступной в банкомате (после снятия)
    $result = mysqli_query($dbConnection, $query);
    $dataRow = mysqli_fetch_row($result); // получение строки результирующей таблицы в виде массива
    $availSumAfter = $dataRow [0]; // сумма доступная в банкомате (после снятия)

    echo "Получите $getSum грн </br></br>";
    echo "Число купюр: $numberBanknotes</br></br>";
    echo "Еще доступно $availSumAfter грн </br></br>";
    buttonBack();
}

$query = "INSERT INTO `logs` (`date`, `name`, `sum`, `balance_before`, `balance_after`, `remark`) 
    VALUES ('{$date}','{$enteredName}', '{$requestSum}', '{$availSumBefore}', '{$availSumAfter}', '{$remark}')";
$result = mysqli_query($dbConnection, $query);

//FUNCTIONS-------------------------------------------------------------------------------------------------------------

function validateName ($enteredName) { // ф-ция для проверки корректности ввода имени пользователем
    $errorValidation = null; // этой переменной присваиваем описание ошибки при некорректном вводе имени
    $flag = preg_match("/^[a-z]{1}[a-z]*[-\s]?[a-z]{1}$/i", $enteredName); // имя неправильное если введено: < 2 символов, тире > 1 шт., пробел > 1 шт., символ не латиницей или цифра
    if (!$flag) {
        $errorValidation = 'Неверно указано имя: имя может иметь от 2 до 20 символов латинского алфавита, допускается использование одного пробела или тире в середине имени';
    }
    return $errorValidation;
}

function validateSum ($requestSum) { // ф-ция для проверки корректности ввода суммы пользователем
    $errorValidation = null; // этой переменной присваиваем описание ошибки при некорректном вводе суммы
    switch (true) {
        case (empty ($requestSum)): // пользователь ввел 0 либо отправил пустую строку
            $errorValidation = 'Неверно указана сумма: ноль выдать невозможно либо поле для ввода суммы пустое';
            break;
        case (!is_numeric ($requestSum)): // пользователь ввел нечисловой символ
            $errorValidation = 'Неверно указана сумма: допускается ввод суммы только цифрами';
            break;
        case (!filter_var($requestSum, FILTER_VALIDATE_INT)): // пользователь ввел сумму дробным числом
            $errorValidation = 'Неверно указана сумма: не допускается ввод суммы дробным числом';
            break;
        case ($requestSum % 5 !== 0): // если при делении суммы на 5 есть остаток, сумма не кратна 5
            $errorValidation = 'Неверно указана сумма: сумма должна быть кратной 5';
    }
    return $errorValidation;
}

function verifySum ($requestSum, $availableSum) {  // ф-ция для проверки наличия требуемой суммы в банкомате
    $errorVerification = null;
    if ($requestSum > $availableSum) {
        $errorVerification = 'Указанная сумма не может быть выдана: в банкомате недостаточно денежных средств';
    }
    return $errorVerification;
}

function searchBanknotes ($dataArr, $residueSum) { //ф-ция проверяющая наличие требуемых банкнот в банкомате
    for ($i = 0; $i < count ($dataArr); $i++) {
        $denomination = $dataArr [$i] ['denomination']; // номинал купюр
        $banknotes = $dataArr [$i] ['number_banknotes']; // кол-во доступных банкнот каждого нминала
        while ($banknotes && $residueSum >= $denomination) {
            $residueSum -= $denomination;
            $banknotes--;
        }
    }
    if ($residueSum) {
        return "Не осталось необходимых купюр";
    } else {
        return false;
    }
}

function buttonBack () { // кнопка "Назад"
    echo '<form action="index.php">';
    echo '<button>Назад</button>';
    echo '<form>';
}