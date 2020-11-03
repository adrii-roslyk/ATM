<?php
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
    <body>
        <?php
        $query = "SELECT SUM(`denomination` * `number_banknotes`) FROM `available_banknotes`";
        $result = mysqli_query($dbConnection, $query);
        $row = mysqli_fetch_row($result);
        $availableSum = $row [0];
        echo "<div><p>Доступно: $availableSum грн</p></div>";
        ?>
        <form action="processing.php" method="post">
            <div><label for="input1">Введите имя латиницей:</label></div><br>
            <div><input type="text" name="name" id="input1" maxlength="20" size="24" autofocus></div><br>
            <div><label for="input2">Введите сумму:</label></div><br>
            <div><input type="text" name="sum" id="input2" maxlength="6" size="5"></div><br>
            <div><input type="submit" value="Получить"></div>
        </form>
    </body>
</html>
