<?php
$baseUrl = 'http://localhost:8000/api';

function post($url, $data, $token = null) {
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($resp, true)];
}

function get($url, $token = null) {
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($resp, true)];
}

echo "1. Logging in...\n";
$login = post("$baseUrl/login", ['email' => 'tes@yaya.com', 'password' => 'tes1234567']);
if ($login['code'] !== 200) {
    die("Login failed: " . json_encode($login));
}
$token = $login['body']['token'] ?? $login['body']['data']['token'] ?? null;
if (!$token) {
    // try to find token in output
    if(isset($login['body']['access_token'])) {
        $token = $login['body']['access_token'];
    } elseif (isset($login['body']['data']['access_token'])) {
        $token = $login['body']['data']['access_token'];
    } else {
        die("Token not found in response: " . json_encode($login));
    }
}
echo "Login OK\n";

echo "2. Fetching Journals...\n";
$journals = get("$baseUrl/journals", $token);
if ($journals['code'] !== 200) {
    die("Failed to fetch journals: " . json_encode($journals));
}
$journalId = $journals['body']['data'][0]['id'] ?? null;
if (!$journalId) die("No journals found");
echo "Selected Journal ID: $journalId\n";

echo "3. Creating Submission...\n";
$subData = [
    'journal_id' => $journalId,
    'title' => 'Runtime Verification Submission — Author Workspace',
    'abstract' => 'This is a runtime verification submission created through the existing HexaLMS Author Workspace workflow.',
    'keywords' => ['runtime verification', 'author workspace', 'hexalms'],
    'authors' => [
        [
            'first_name' => 'Tes',
            'last_name' => 'User',
            'email' => 'tes@yaya.com',
            'affiliation' => 'Hexa',
            'is_corresponding' => true
        ]
    ]
];
$create = post("$baseUrl/submissions", $subData, $token);
if ($create['code'] !== 201) {
    die("Failed to create submission: " . json_encode($create));
}
$subId = $create['body']['data']['id'] ?? $create['body']['id'] ?? null;
echo "Created Submission ID: $subId\n";

echo "4. Verifying /author/submissions via API...\n";
$list = get("$baseUrl/submissions", $token);
if ($list['code'] !== 200) {
    die("Failed to fetch submissions: " . json_encode($list));
}
$found = false;
foreach ($list['body']['data'] as $s) {
    if ($s['id'] === $subId) {
        $found = true;
        break;
    }
}
echo "Submission in list: " . ($found ? "YES" : "NO") . "\n";

echo "5. Verifying Submission Detail...\n";
$detail = get("$baseUrl/submissions/$subId", $token);
echo "Detail Code: " . $detail['code'] . "\n";
echo "Detail Title: " . $detail['body']['data']['title'] . "\n";
echo "Detail Status: " . $detail['body']['data']['status'] . "\n";

