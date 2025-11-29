<?php
function scanWebsite($url, $keywords) {
    $context = stream_context_create([
        "http" => [
            "timeout" => 12,
            "header"  => "User-Agent: Mozilla/5.0"
        ]
    ]);

    $content = @file_get_contents($url, false, $context);

    if ($content === false) {
        return [
            "error" => true,
            "score" => 100,
            "found_keywords" => [],
            "hidden" => false,
            "outbound" => 0
        ];
    }

    $contentLower = strtolower($content);

    $foundKeywords = [];
    foreach ($keywords as $keyword) {
        if ($keyword !== "" && strpos($contentLower, strtolower(trim($keyword))) !== false) {
            $foundKeywords[] = $keyword;
        }
    }

    $hiddenPatterns = [
        'display\s*:\s*none',
        'visibility\s*:\s*hidden',
        'opacity\s*:\s*0',
        'left\s*:\s*-?9999'
    ];

    $hiddenFound = false;
    foreach ($hiddenPatterns as $pattern) {
        if (preg_match('/' . $pattern . '/i', $content)) {
            $hiddenFound = true;
            break;
        }
    }

    preg_match_all('/<a[^>]+href=["\']([^"\']+)/i', $content, $links);
    $outboundCount = count($links[1]);
    $outboundSuspicious = $outboundCount > 20;

    $score = 0;

    if (!empty($foundKeywords)) $score += 50;
    if ($hiddenFound) $score += 30;
    if ($outboundSuspicious) $score += 10;

    return [
        "error" => false,
        "score" => $score,
        "found_keywords" => $foundKeywords,
        "hidden" => $hiddenFound,
        "outbound" => $outboundCount
    ];
}

$results = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $urlsRaw = $_POST["urls"] ?? "";
    $urls = array_filter(array_map("trim", explode("\n", $urlsRaw)));

    $keywords = file_exists("keywords.txt")
        ? file("keywords.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    foreach ($urls as $url) {
        $results[$url] = scanWebsite($url, $keywords);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scanner Backlink Judol</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        textarea { width: 100%; height: 200px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #999; padding: 8px; font-size: 14px; }
        th { background: #eee; }
        .red   { color: red; font-weight: bold; }
        .green { color: green; font-weight: bold; }
        .orange { color: #ff8800; font-weight: bold; }
        .bad { background: #ffe5e5; }
        .good { background: #e5ffe5; }
        .warn { background: #fff3cd; }
    </style>
</head>
<body>

<h2>Scanner Backlink Judol — Heuristic Scoring System</h2>

<form method="POST">
    <label>Masukkan daftar URL (1 per baris):</label><br>
    <textarea name="urls"><?php echo $_POST["urls"] ?? ""; ?></textarea><br><br>
    <button type="submit">SCAN SEKARANG</button>
</form>

<?php if (!empty($results)): ?>
    <h3>Hasil Scan:</h3>
    <table>
        <tr>
            <th>URL</th>
            <th>Status</th>
            <th>Skor</th>
            <th>Kata Kunci</th>
            <th>Backlink Tersembunyi</th>
            <th>Outbound Link</th>
        </tr>

        <?php foreach ($results as $url => $res): ?>
            <?php
                if ($res["error"]) {
                    $status = "<span class='red'>Gagal Diakses</span>";
                    $rowClass = "bad";
                } else if ($res["score"] >= 50) {
                    $status = "<span class='red'>TERINFEKSI</span>";
                    $rowClass = "bad";
                } else if ($res["score"] >= 30) {
                    $status = "<span class='orange'>MENCURIGAKAN</span>";
                    $rowClass = "warn";
                } else {
                    $status = "<span class='green'>AMAN</span>";
                    $rowClass = "good";
                }
            ?>

            <tr class="<?php echo $rowClass; ?>">
                <td><?php echo htmlspecialchars($url); ?></td>
                <td><?php echo $status; ?></td>
                <td><?php echo $res["score"]; ?></td>
                <td><?php echo empty($res["found_keywords"]) ? "-" : implode(", ", $res["found_keywords"]); ?></td>
                <td><?php echo $res["hidden"] ? "Ya" : "-"; ?></td>
                <td><?php echo $res["outbound"]; ?></td>
            </tr>
        <?php endforeach; ?>

    </table>
<?php endif; ?>

</body>
</html>
