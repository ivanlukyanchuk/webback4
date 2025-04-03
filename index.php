<?php
// Устанавливаем кодировку UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Если метод запроса GET, просто отображаем форму
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Если есть параметр save, выводим сообщение об успешном сохранении
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }
    // Подключаем файл с формой
    include('form.php');
    exit();
}

// Иначе, если запрос был методом POST, проверяем данные и сохраняем их в БД

// Инициализируем массив для ошибок
$errors = [];

// Проверка поля ФИО
if (empty($_POST['full_name'])) {
    $errors[] = 'Заполните ФИО.';
} elseif (!preg_match('/^[a-zA-Zа-яА-Я\s]{1,150}$/u', $_POST['full_name'])) {
    $errors[] = 'ФИО должно содержать только буквы и пробелы и быть не длиннее 150 символов.';
}

// Проверка поля Телефон
if (empty($_POST['phone'])) {
    $errors[] = 'Заполните телефон.';
} elseif (!preg_match('/^\+?\d{10,15}$/', $_POST['phone'])) {
    $errors[] = 'Телефон должен быть в формате +7XXXXXXXXXX или XXXXXXXXXX.';
}

// Проверка поля Email
if (empty($_POST['email'])) {
    $errors[] = 'Заполните email.';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email.';
}

// Проверка поля Дата рождения
if (empty($_POST['dob'])) {
    $errors[] = 'Заполните дату рождения.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
    $errors[] = 'Некорректный формат даты рождения.';
}

// Проверка поля Пол
if (empty($_POST['gender'])) {
    $errors[] = 'Выберите пол.';
} elseif (!in_array($_POST['gender'], ['male', 'female'])) {
    $errors[] = 'Некорректное значение пола.';
}

// Проверка поля Языки программирования
if (empty($_POST['languages'])) {
    $errors[] = 'Выберите хотя бы один язык программирования.';
} else {
    $allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    foreach ($_POST['languages'] as $language) {
        if (!in_array($language, $allowedLanguages)) {
            $errors[] = 'Некорректный язык программирования.';
            break;
        }
    }
}

// Проверка поля Биография
if (empty($_POST['bio'])) {
    $errors[] = 'Заполните биографию.';
}

// Проверка чекбокса "С контрактом ознакомлен"
if (empty($_POST['contract'])) {
    $errors[] = 'Необходимо ознакомиться с контрактом.';
}

// Если есть ошибки, выводим их и завершаем выполнение скрипта
if (!empty($errors)) {
    foreach ($errors as $error) {
        print($error . '<br>');
    }
    exit();
}

// Подключение к базе данных
$user = 'u68581'; // Замените на ваш логин
$pass = '4027467'; // Замените на ваш пароль
$db = new PDO('mysql:host=localhost;dbname=u68581', $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    // Начало транзакции
    $db->beginTransaction();

    // Сохранение основной информации о заявке
    $stmt = $db->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, bio) 
                          VALUES (:full_name, :phone, :email, :birth_date, :gender, :bio)");
    $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['dob'],
        ':gender' => $_POST['gender'],
        ':bio' => $_POST['bio']
    ]);

    // Получение ID последней вставленной записи
    $application_id = $db->lastInsertId();

    // Сохранение выбранных языков программирования
    $stmt = $db->prepare("SELECT id FROM languages WHERE name = :name");
    $insertLang = $db->prepare("INSERT INTO languages (name) VALUES (:name)");
    $linkStmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) 
                              VALUES (:application_id, :language_id)");

    foreach ($_POST['languages'] as $language) {
        $stmt->execute([':name' => $language]);
        $languageData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$languageData) {
            $insertLang->execute([':name' => $language]);
            $language_id = $db->lastInsertId();
        } else {
            $language_id = $languageData['id'];
        }

        $linkStmt->execute([
            ':application_id' => $application_id,
            ':language_id' => $language_id
        ]);
    }

    // Завершение транзакции
    $db->commit();

    // Перенаправление на страницу с сообщением об успешном сохранении
    header('Location: ?save=1');
} catch (PDOException $e) {
    // Откат транзакции в случае ошибки
    $db->rollBack();
    print('Ошибка при сохранении данных: ' . $e->getMessage());
    exit();
}
?>
