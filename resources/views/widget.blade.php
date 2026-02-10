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

        /* Модальное окно */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 40px;
            max-width: 650px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            font-size: 20.8px; /* увеличение на 30% от базового 16px */
            line-height: 1.5;
        }

        .modal-content.success {
            background: #d4edda; /* светло-зеленый */
            border-top: 4px solid #28a745;
        }

        .modal-content.error {
            background: #fce4ec; /* розовый */
            border-top: 4px solid #dc3545;
        }

        .modal-content.info {
            background: #d1ecf1;
            border-top: 4px solid #17a2b8;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
            line-height: 1;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal-title {
            font-size: 26px; /* увеличение на 30% */
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .modal-message {
            font-size: 20.8px; /* увеличение на 30% */
            color: #555;
            margin-bottom: 10px;
        }

        .modal-details {
            margin-top: 15px;
            font-size: 16.9px; /* увеличение на 30% от 13px */
            color: #666;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
        }

        .message {
            display: none !important; /* Скрыто, используется только модальное окно */
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
    <!-- Модальное окно для сообщений -->
    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-content" class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-title" id="modal-title"></div>
            <div class="modal-message" id="modal-message"></div>
            <div class="modal-details" id="modal-details"></div>
        </div>
    </div>

    <div class="widget-container">
        <h1>Обратная связь</h1>

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
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

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
                    const successMessage = 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.';
                    form.reset();
                    
                    // Если виджет встроен в iframe - отправляем сообщение родительской странице
                    // Если виджет открыт напрямую - показываем модальное окно здесь
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'ticket-success',
                            message: successMessage,
                            data: data
                        }, '*');
                    } else {
                        showModal('success', 'Успешно!', successMessage, data);
                    }
                } else {
                    // Ошибка
                    let errorMessage = 'Произошла ошибка при отправке заявки ' + data.message;

                    // Если есть ошибки валидации
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join(', ');
                        errorMessage = 'Ошибки валидации: ' + errorList;
                    }

                    // Если виджет встроен в iframe - отправляем сообщение родительской странице
                    // Если виджет открыт напрямую - показываем модальное окно здесь
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'ticket-error',
                            message: errorMessage,
                            data: data
                        }, '*');
                    } else {
                        showModal('error', 'Ошибка', errorMessage, data);
                    }
                }
            } catch (error) {
                const errorMessage = 'Произошла ошибка при отправке заявки. Попробуйте позже.';
                
                // Если виджет встроен в iframe - отправляем сообщение родительской странице
                // Если виджет открыт напрямую - показываем модальное окно здесь
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'ticket-error',
                        message: errorMessage,
                        error: error.message
                    }, '*');
                } else {
                    showModal('error', 'Ошибка', errorMessage, { error: error.message });
                }
            } finally {
                // Разблокируем кнопку
                submitBtn.disabled = false;
                submitBtn.textContent = 'Отправить';
            }
        });

        function showModal(type, title, message, details = null) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            const titleEl = document.getElementById('modal-title');
            const messageEl = document.getElementById('modal-message');
            const detailsEl = document.getElementById('modal-details');

            // Убираем предыдущие классы
            content.classList.remove('success', 'error', 'info');
            content.classList.add(type);

            // Устанавливаем содержимое
            titleEl.textContent = title;
            messageEl.textContent = message;
            detailsEl.textContent = '';

            // Показываем детали, если есть
            if (details) {
                const detailsText = JSON.stringify(details, null, 2);
                detailsEl.textContent = 'Детали:\n' + detailsText;
            }

            // Показываем модальное окно
            overlay.classList.add('show');

            // Автоматически закрываем через 10 секунд (для успеха) или 15 секунд (для ошибки)
            const timeout = type === 'success' ? 10000 : 15000;
            setTimeout(() => {
                closeModal();
            }, timeout);
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            overlay.classList.remove('show');
        }

        // Закрытие по клику на overlay
        document.getElementById('modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>

