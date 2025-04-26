<?php
/**
 * Обработчик для стандартизации ФИО через API DaData
 */

// Получаем данные из формы (поддерживаются как POST, так и GET запросы)
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

// Проверяем наличие необходимых параметров
if (!isset($requestData['surname']) || !isset($requestData['name'])) {
    echo "Не заполнены обязательные поля (фамилия и имя)";
    exit;
}

// Получаем API-ключи из скрытых полей формы или используем заданные в коде
$apiKey = isset($requestData['api_key']) ? $requestData['api_key'] : '1437ebd34d55ec8ebf3e2274121e4cf060ea80ed';
$secretKey = isset($requestData['secret_key']) ? $requestData['secret_key'] : '879ad673171fbd0ab49fb996cb823dfdc01299b6';

// Получаем данные из формы
$surname = $requestData['surname'];
$name = $requestData['name'];
$patronymic = isset($requestData['patronymic']) ? $requestData['patronymic'] : '';

// Формируем полное ФИО
$fullName = "$surname $name $patronymic";

// Удаляем лишние пробелы
$fullName = trim($fullName);

// Формируем запрос к API DaData
$url = "https://cleaner.dadata.ru/api/v1/clean/name";
$data = [$fullName];

// Выполняем запрос к API
$result = sendRequest($url, $data, $apiKey, $secretKey);

// Проверяем результат запроса
if (isset($result[0])) {
    $cleanedData = $result[0];
} else {
    echo "Ошибка при обработке данных";
    exit;
}

/**
 * Отправляет запрос к API DaData
 *
 * @param string $url URL для запроса
 * @param array $data Данные для запроса
 * @param string $apiKey API-ключ
 * @param string $secretKey Секретный ключ
 * @return array Результат запроса
 */
function sendRequest($url, $data, $apiKey, $secretKey) {
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $apiKey,
                'X-Secret: ' . $secretKey
            ],
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "Ошибка при отправке запроса к API";
        exit;
    }
    
    return json_decode($response, true);
}

// Функция для вывода значения или прочерка, если значение пустое
function valueOrDash($value) {
    return !empty($value) ? $value : '—';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат стандартизации ФИО</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .result {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .result-item {
            margin-bottom: 10px;
        }
        .result-item strong {
            display: inline-block;
            width: 200px;
        }
        .original {
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #a3e4c5;
            margin-bottom: 20px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Результат стандартизации ФИО</h1>
    
    <div class="original">
        <h3>Исходные данные:</h3>
        <div class="result-item"><strong>Фамилия:</strong> <?php echo htmlspecialchars($surname); ?></div>
        <div class="result-item"><strong>Имя:</strong> <?php echo htmlspecialchars($name); ?></div>
        <div class="result-item"><strong>Отчество:</strong> <?php echo htmlspecialchars($patronymic); ?></div>
    </div>
    
    <div class="result">
        <h3>Стандартизированные данные:</h3>
        <div class="result-item"><strong>Фамилия:</strong> <?php echo valueOrDash($cleanedData['surname']); ?></div>
        <div class="result-item"><strong>Имя:</strong> <?php echo valueOrDash($cleanedData['name']); ?></div>
        <div class="result-item"><strong>Отчество:</strong> <?php echo valueOrDash($cleanedData['patronymic']); ?></div>
        <div class="result-item"><strong>Пол:</strong> <?php echo valueOrDash($cleanedData['gender'] === 'М' ? 'Мужской' : ($cleanedData['gender'] === 'Ж' ? 'Женский' : 'Не определен')); ?></div>
        <div class="result-item"><strong>Полное имя (ФИО):</strong> <?php echo valueOrDash($cleanedData['result']); ?></div>
        <div class="result-item"><strong>В родительном падеже:</strong> <?php echo valueOrDash($cleanedData['result_genitive']); ?></div>
        <div class="result-item"><strong>В дательном падеже:</strong> <?php echo valueOrDash($cleanedData['result_dative']); ?></div>
        <div class="result-item"><strong>В творительном падеже:</strong> <?php echo valueOrDash($cleanedData['result_ablative']); ?></div>
    </div>
    
    <a href="form.html" class="back-link">Вернуться к форме</a>
</body>
</html>
