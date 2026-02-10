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
    </style>
</head>
<body>
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
</body>
</html>

