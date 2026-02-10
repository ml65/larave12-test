<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обратная связь</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .widget-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: #007bff;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .required {
            color: #dc3545;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover:not(:disabled) {
            background: #0056b3;
        }

        button[type="submit"]:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.show {
            display: block;
        }

        .error-list {
            margin-top: 5px;
            color: #dc3545;
            font-size: 12px;
        }

        .error-list li {
            list-style: none;
        }
    </style>
</head>
<body>
    <div class="widget-container">
        <h1>Обратная связь</h1>

        <div id="message" class="message"></div>

        <form id="ticket-form">
            <div class="form-group">
                <label for="name">Имя <span class="required">*</span></label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" placeholder="+1234567890" required>
                <small style="color: #6c757d; font-size: 12px;">Формат: +1234567890 (E.164)</small>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="subject">Тема <span class="required">*</span></label>
                <input type="text" id="subject" name="subject" required>
            </div>

            <div class="form-group">
                <label for="text">Сообщение <span class="required">*</span></label>
                <textarea id="text" name="text" required></textarea>
            </div>

            <div class="form-group">
                <label for="files">Файлы (опционально)</label>
                <input type="file" id="files" name="files[]" multiple>
                <small style="color: #6c757d; font-size: 12px;">Максимум 10 МБ на файл</small>
            </div>

            <button type="submit" id="submit-btn">Отправить</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('ticket-form');
        const messageDiv = document.getElementById('message');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Скрываем предыдущие сообщения
            messageDiv.classList.remove('show', 'success', 'error');
            messageDiv.textContent = '';

            // Блокируем кнопку
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';

            // Создаем FormData
            const formData = new FormData(form);

            try {
                const response = await fetch('/api/tickets', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    // Успешная отправка
                    messageDiv.textContent = 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.';
                    messageDiv.classList.add('show', 'success');
                    form.reset();
                    
                    // Отправляем сообщение родительской странице
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'ticket-success',
                            message: 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
                            data: data
                        }, '*');
                    }
                } else {
                    // Ошибка
                    let errorMessage = data.message || 'Произошла ошибка при отправке заявки.';

                    // Если есть ошибки валидации
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join(', ');
                        errorMessage = 'Ошибки валидации: ' + errorList;
                    }

                    messageDiv.textContent = errorMessage;
                    messageDiv.classList.add('show', 'error');
                    
                    // Отправляем сообщение родительской странице
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'ticket-error',
                            message: errorMessage,
                            data: data
                        }, '*');
                    }
                }
            } catch (error) {
                messageDiv.textContent = 'Произошла ошибка при отправке заявки. Попробуйте позже.';
                messageDiv.classList.add('show', 'error');
                
                // Отправляем сообщение родительской странице
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'ticket-error',
                        message: 'Произошла ошибка при отправке заявки. Попробуйте позже.',
                        error: error.message
                    }, '*');
                }
            } finally {
                // Разблокируем кнопку
                submitBtn.disabled = false;
                submitBtn.textContent = 'Отправить';
            }
        });
    </script>
</body>
</html>

