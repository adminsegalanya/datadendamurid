<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patungan Bareng ke Bogor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Patungan Bareng ke Bogor</h1>
        
        <!-- Form Input -->
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="date">Tanggal</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="amount">Jumlah Tabungan</label>
                <select id="amount" name="amount" required>
                    <option value="10000">10.000</option>
                    <option value="6000">6.000</option>
                    <option value="2000">2.000</option>
                </select>
            </div>
            <button type="submit" name="add">Tambah Tabungan</button>
        </form>

        <?php
        $apiUrl = 'https://api.sheetbest.com/sheets/c3dec71b-f6a4-406a-8c20-e9c420dd08f0';

        // Fungsi untuk mengirim request ke SheetBest
        function sendRequest($url, $method = 'GET', $data = null) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        }

        // Tambah data
        if (isset($_POST['add'])) {
            $name = $_POST['name'];
            $date = $_POST['date'];
            $amount = $_POST['amount'];

            // Ambil data untuk menentukan ID terakhir
            $data = sendRequest($apiUrl);
            $lastId = empty($data) ? 0 : max(array_column($data, 'ID'));

            // Tambah entri baru
            $newRow = [
                'ID' => $lastId + 1,
                'Nama' => $name,
                'Tanggal' => $date,
                'Jumlah' => (int)$amount
            ];
            sendRequest($apiUrl, 'POST', $newRow);
        }

        // Hapus data
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            sendRequest("$apiUrl/$id", 'DELETE');
        }

        // Ambil dan kelompokkan data
        $data = sendRequest($apiUrl);
        $groupedData = [];
        if (!empty($data)) {
            foreach ($data as $row) {
                $name = $row['Nama'];
                if (!isset($groupedData[$name])) {
                    $groupedData[$name] = [];
                }
                $groupedData[$name][] = [
                    'id' => $row['ID'],
                    'date' => $row['Tanggal'],
                    'amount' => $row['Jumlah']
                ];
            }
        }

        // Tampilkan data per anak
        foreach ($groupedData as $name => $savings) {
            echo "<h2>$name</h2>";
            echo "<table>";
            echo "<tr><th>Tanggal</th><th>Jumlah</th><th>Aksi</th></tr>";
            $total = 0;
            foreach ($savings as $row) {
                $amount = (int)$row['amount'];
                $total += $amount;
                echo "<tr>";
                echo "<td>" . $row['date'] . "</td>";
                echo "<td>Rp " . number_format($amount, 0, ',', '.') . "</td>";
                echo "<td><a href='index.php?delete=" . $row['id'] . "' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a></td>";
                echo "</tr>";
            }
            echo "</table>";

            // Ringkasan
            $goal = 400000;
            $shortage = $goal - $total;
            echo "<div class='summary'>";
            echo "<p><strong>Total Tabungan:</strong> Rp " . number_format($total, 0, ',', '.') . "</p>";
            echo "<p><strong>Kekurangan:</strong> Rp " . number_format($shortage, 0, ',', '.') . "</p>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const date = document.getElementById('date').value;
            if (!name || !date) {
                alert('Nama dan tanggal harus diisi!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
