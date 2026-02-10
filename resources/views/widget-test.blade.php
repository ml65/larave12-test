<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест виджета обратной связи</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .demo-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .demo-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .demo-section p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .code-block {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .code-block code {
            color: #333;
        }

        .widget-container {
            margin: 30px 0;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 20px;
            background: #f9f9f9;
        }

        .widget-container iframe {
            width: 100%;
            border: none;
            border-radius: 8px;
            min-height: 600px;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-box strong {
            color: #1976d2;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
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

        .widget-message {
            display: none !important; /* Скрыто, используется только модальное окно */
        }

        .widget-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .widget-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .widget-message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .widget-message-close {
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 15px;
            opacity: 0.7;
        }

        .widget-message-close:hover {
            opacity: 1;
        }

        .widget-message-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .widget-message-text {
            flex: 1;
        }

        .widget-message-details {
            margin-top: 10px;
            font-size: 12px;
            opacity: 0.8;
            max-height: 100px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
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

    <div id="widget-message" class="widget-message">
        <div class="widget-message-content">
            <div class="widget-message-text">
                <div id="widget-message-text"></div>
                <div id="widget-message-details" class="widget-message-details"></div>
            </div>
            <span class="widget-message-close" onclick="closeWidgetMessage()">&times;</span>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>🧪 Тест виджета обратной связи</h1>
            <p>Демонстрация встраивания виджета через iframe</p>
        </div>

        <div class="demo-section">
            <h2>Пример встраивания виджета</h2>
            <p>
                Ниже показан виджет обратной связи, встроенный через iframe.
                Это демонстрирует, как виджет будет выглядеть на реальном сайте.
            </p>

            <div class="info-box">
                <strong>Информация:</strong> Виджет доступен по адресу 
                <code>{{ route('widget') }}</code> и может быть встроен на любой сайт.
                <br>Возврат в админ-панель по адресу <code><a href="{{ route('admin.tickets.index') }}">{{ route('admin.tickets.index') }}</a></code>
            </div>

            <div class="widget-container">
                <iframe 
                    src="{{ route('widget') }}" 
                    title="Виджет обратной связи"
                    allow="camera; microphone">
                </iframe>
            </div>
        </div>

        <div class="demo-section">
            <h2>Код для встраивания</h2>
            <p>Используйте следующий код для встраивания виджета на ваш сайт:</p>
            
            <div class="code-block">
                <code>&lt;iframe 
    src="{{ url('/widget') }}" 
    width="100%" 
    height="600" 
    frameborder="0"
    style="border: none;"
    allow="camera; microphone"&gt;
&lt;/iframe&gt;</code>
            </div>

            <p>
                <strong>Параметры:</strong>
            </p>
            <ul style="color: #666; margin-left: 20px; line-height: 1.8;">
                <li><code>src</code> - URL виджета ({{ url('/widget') }})</li>
                <li><code>width</code> - ширина виджета (рекомендуется 100% или минимум 400px)</li>
                <li><code>height</code> - высота виджета (рекомендуется 600px или больше)</li>
                <li><code>frameborder</code> - убирает рамку вокруг iframe</li>
                <li><code>allow</code> - разрешения для доступа к камере и микрофону (для загрузки файлов)</li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>Адаптивная версия</h2>
            <p>Для адаптивного дизайна используйте следующий код:</p>
            
            <div class="code-block">
                <code>&lt;iframe 
    src="{{ url('/widget') }}" 
    width="100%" 
    height="600" 
    frameborder="0"
    style="border: none; max-width: 600px; margin: 0 auto; display: block;"
    allow="camera; microphone"&gt;
&lt;/iframe&gt;</code>
            </div>
        </div>

        <a href="{{ url('/') }}" class="back-link">← Вернуться на главную</a>
    </div>

    <script>
        // Блок для отображения сообщений от виджета
        const widgetMessageDiv = document.getElementById('widget-message');
        const widgetMessageText = document.getElementById('widget-message-text');
        const widgetMessageDetails = document.getElementById('widget-message-details');

        // Слушаем сообщения от виджета
        window.addEventListener('message', function(event) {
            // Проверяем, что сообщение от нашего виджета (можно добавить проверку origin)
            if (event.data && (event.data.type === 'ticket-success' || event.data.type === 'ticket-error')) {
                showWidgetMessage(event.data);
            }
        });

        function showWidgetMessage(data) {
            // Используем модальное окно вместо старого сообщения
            const type = data.type === 'ticket-success' ? 'success' : 'error';
            const title = data.type === 'ticket-success' ? 'Успешно!' : 'Ошибка';
            const details = data.data || (data.error ? { error: data.error } : null);
            
            showModal(type, title, data.message, details);
        }

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

        function closeWidgetMessage() {
            const widgetMessageDiv = document.getElementById('widget-message');
            widgetMessageDiv.classList.remove('show');
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

