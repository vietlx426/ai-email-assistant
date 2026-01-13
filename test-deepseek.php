<?php
// Save this as test-deepseek.php in your project root

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';

if (!$apiKey || $apiKey === 'sk-your-actual-api-key-here') {
    echo "❌ Error: Please add your actual DeepSeek API key to the .env file\n";
    echo "   DEEPSEEK_API_KEY=sk-your-actual-key-here\n";
    exit(1);
}

echo "🔍 Testing DeepSeek API Connection...\n";
echo "   API Key: " . substr($apiKey, 0, 10) . "...\n\n";

$client = new GuzzleHttp\Client();

try {
    $response = $client->post('https://api.deepseek.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => 'Say "Hello! DeepSeek is working!" if you can read this.']
            ],
            'max_tokens' => 50,
            'temperature' => 0.7,
        ],
        'timeout' => 30,
    ]);

    $data = json_decode($response->getBody()->getContents(), true);

    echo "✅ Success! DeepSeek API is working!\n\n";
    echo "📝 Response: " . $data['choices'][0]['message']['content'] . "\n";
    echo "📊 Model: " . $data['model'] . "\n";
    echo "💰 Tokens used: " . $data['usage']['total_tokens'] . "\n";

} catch (GuzzleHttp\Exception\ClientException $e) {
    $response = $e->getResponse();
    $body = json_decode($response->getBody()->getContents(), true);

    echo "❌ API Error: " . ($body['error']['message'] ?? 'Unknown error') . "\n";

    if (str_contains($body['error']['message'] ?? '', 'Invalid API key')) {
        echo "   Please check your API key in the .env file\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}