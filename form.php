<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма</title>
    <style>
                body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="radio"] {
            margin-right: 10px;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .radio-group {
            margin-bottom: 20px;
        }

        .radio-group label {
            display: inline;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group label {
            margin-left: 10px;
            padding-top: 10px;
        }

        .error {
            border: 2px solid red;
        }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
    <div class="form-container">
        <h2>Форма</h2>

        <?php
        // Вывод сообщений об ошибках
        if (!empty($messages)) {
            print('<div id="messages">');
            foreach ($messages as $message) {
                print($message);
            }
            print('</div>');
        }
        ?>

        <form action="" method="POST">
            <!-- Поле ФИО -->
            <label for="fio">ФИО:</label>
            <input type="text" name="fio" id="fio" value="<?php print $values['fio']; ?>" <?php if ($errors['fio']) {print 'class="error"';} ?>>
            <?php if ($errors['fio']) {print '<div class="error-message">Недопустимые символы в ФИО. Используйте только буквы и пробелы.</div>';} ?>

            <!-- Поле Телефон -->
            <label for="phone">Телефон:</label>
            <input type="tel" name="phone" id="phone" value="<?php print $values['phone']; ?>" <?php if ($errors['phone']) {print 'class="error"';} ?>>
            <?php if ($errors['phone']) {print '<div class="error-message">Телефон должен быть в формате +7XXXXXXXXXX или XXXXXXXXXX.</div>';} ?>

            <!-- Поле Email -->
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php print $values['email']; ?>" <?php if ($errors['email']) {print 'class="error"';} ?>>
            <?php if ($errors['email']) {print '<div class="error-message">Некорректный email.</div>';} ?>

            <!-- Поле Дата рождения -->
            <label for="dob">Дата рождения:</label>
            <input type="date" name="dob" id="dob" value="<?php print $values['dob']; ?>" <?php if ($errors['dob']) {print 'class="error"';} ?>>
            <?php if ($errors['dob']) {print '<div class="error-message">Некорректный формат даты рождения.</div>';} ?>

            <!-- Поле Пол -->
            <label>Пол:</label>
            <div class="radio-group">
                <input type="radio" name="gender" value="male" id="male" <?php if ($values['gender'] == 'male') {print 'checked';} ?>>
                <label for="male">Мужской</label>
                <input type="radio" name="gender" value="female" id="female" <?php if ($values['gender'] == 'female') {print 'checked';} ?>>
                <label for="female">Женский</label>
            </div>
            <?php if ($errors['gender']) {print '<div class="error-message">Выберите пол.</div>';} ?>

            <!-- Поле Любимый язык программирования -->
            <label for="languages">Любимый язык программирования:</label>
            <select name="languages[]" id="languages" multiple>
                <?php
                $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                foreach ($languages as $language) {
                    $selected = in_array($language, $values['languages']) ? 'selected' : '';
                    print("<option value='$language' $selected>$language</option>");
                }
                ?>
            </select>
            <?php if ($errors['languages']) {print '<div class="error-message">Выберите хотя бы один язык программирования.</div>';} ?>

            <!-- Поле Биография -->
            <label for="bio">Биография:</label>
            <textarea name="bio" id="bio" rows="5" cols="40" <?php if ($errors['bio']) {print 'class="error"';} ?>><?php print $values['bio']; ?></textarea>
            <?php if ($errors['bio']) {print '<div class="error-message">Заполните биографию.</div>';} ?>

            <!-- Чекбокс "С контрактом ознакомлен" -->
            <div class="checkbox-group">
                <input type="checkbox" name="contract" id="contract" <?php if ($values['contract']) {print 'checked';} ?>>
                <label for="contract">С контрактом ознакомлен:</label>
            </div>
            <?php if ($errors['contract']) {print '<div class="error-message">Необходимо ознакомиться с контрактом.</div>';} ?>

            <!-- Кнопка отправки формы -->
            <input type="submit" value="Сохранить">
        </form>
    </div>
</body>
</html>
