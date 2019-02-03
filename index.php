<?php
    if(isset($_POST['source'])){
        $source = $_POST['source'];

        if(empty($source)){
            echo 'Page source tidak boleh kosong.';
        }else{
            $jadwals = ParseSource($source);
            echo '<table border="1">';
            echo '<caption>Jadwal Kuliah</caption>';
            echo '<tr>';
            echo '<th colspan="2" style="text-align:center;" bgcolor="#AAAAAA">' . getNama($source) . '</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th style="text-align:center;" bgcolor="#BBBBBB">Hari</th>';
            echo '<th style="text-align:center;" bgcolor="#BBBBBB">Mata Kuliah</th>';
            echo '</tr>';
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('Senin', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">Senin</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'Senin');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('Selasa', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">Selasa</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'Selasa');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('Rabu', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">Rabu</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'Rabu');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('Kamis', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">Kamis</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'Kamis');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('Jumat', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">Jumat</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'Jumat');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            foreach ($jadwals as $jadwal) {
                if(array_key_exists('unknown', $jadwal->jadwal)){
                    echo '<tr>';
                    echo '<td bgcolor="#CCCCCC">(?)</td>';
                    echo '<td bgcolor="#DDDDDD">';
                    cetakJadwal($jadwals, 'unknown');
                    echo '</td>';
                    echo '</tr>';
                    break;
                }
            }
            echo '</table>';
            
        }
    }

    function cetakJadwal($jadwals, $hari){
        $matkulToday = array();
        foreach ($jadwals as $jadwal) {
            if(array_key_exists($hari, $jadwal->jadwal)){
                $matkulToday[] = $jadwal;
            }
        }
        for ($i=0; $i < sizeof($matkulToday); $i++) { 
            for ($j=0; $j < sizeof($matkulToday) - $i - 1; $j++) { 
                if($matkulToday[$j]->getJamMulai($hari) > $matkulToday[$j+1]->getJamMulai($hari)){
                    $temp = $matkulToday[$j];
                    $matkulToday[$j] = $matkulToday[$j+1];
                    $matkulToday[$j+1] = $temp;
                }
            }
        }

        foreach ($matkulToday as $matkul) {
            echo $matkul->nama . ' ' . $matkul->jadwal[$hari] . '<br>';
        }
    }

    /**
     * Entah kenapa di hosting wordpress, double quotes nya harus di escape
     */

    function ParseSource($source){
        $nomor = substr($source, strpos($source, 'nomor\">') + 8, 6);
        $source = strstr($source, 'class=\"hidden-md\">');
        $nama = trim(substr($source, strpos($source, '>') + 1, strpos($source, '<') - strpos($source, '>') - 1));
        $source = strstr($source, 'tbody');
        $source = substr($source, 0, strpos($source, 'tbody', 5));
        $matkul = explode('<tr>', $source);
        $jadwals = array();
        for ($i=1; $i < sizeof($matkul); $i++) { 
            $jadwals[$i-1] = new MataKuliah($matkul[$i]);
        }

        InsertToDatabase($nama, $nomor, $jadwals);

        return $jadwals;
    }

    function getNama($source){
        $nomor = substr($source, strpos($source, 'nomor\">') + 8, 6);
        $source = strstr($source, 'class=\"hidden-md\">');
        $nama = trim(substr($source, strpos($source, '>') + 1, strpos($source, '<') - strpos($source, '>') - 1));
        return $nama;
    }

    function InsertToDatabase($nama, $nomor, $jadwal){
        $servername = "localhost";
        $username = "renziera_jadwalizer_simaster";
        $password = '53wEV%0ta$W#BO6F';
        $database = "renziera_jadwalizer_simaster";
        try{
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
        }
        $insertSQL = "INSERT INTO jadwalizer (nama, niu, jadwal) VALUES (:nama, :niu, :jadwal)";
		$query = $conn->prepare($insertSQL);
        $query->bindParam(':nama', $nama);
        $query->bindParam(':niu', $nomor);
        $query->bindParam(':jadwal', json_encode($jadwal));
		$query->execute();
    
    }

    class MataKuliah{
        var $nama;
        var $jadwal = array();

        function __construct($matkul) {	
            $matkul = strstr($matkul, '<td>');
            $matkul = substr($matkul, strpos($matkul, '<td>', 4) + 4);
            $this->nama = strstr($matkul, '<', true);
            $matkul = strstr($matkul, '<td nowrap>');
            $matkul = substr($matkul, strpos($matkul, '<td nowrap>', 11) + 11);
            $matkul = substr($matkul, strpos($matkul, '<td nowrap>') + 11);
            $matkul = substr($matkul, 0, strpos($matkul, '</td>'));
            $matkuls = explode('<br/>', $matkul);

            foreach ($matkuls as $jadwal) {
                $hari = strstr($jadwal, ',', true);
                $ruang = strstr($jadwal, 'Ruang');
                $ruang = substr($ruang, 6);
                $waktu = substr($jadwal, strpos($jadwal, ' ') + 1, 11);
                if($hari == false){
                    $this->jadwal['unknown'] = '';
                }else{
                    $this->jadwal[$hari] = $waktu . ' | ' . $ruang;
                }
            }
        }
        
        function getJamMulai($hari){
            $jamMulai = $this->jadwal[$hari];
            $jamMulai = substr($jamMulai, 0, 5);
            $jamMulai = str_replace(':', '', $jamMulai);
            return intval($jamMulai);
        }

    }
    
?>

<!DOCTYPE html>
<html>
<style>
    table, th, td {
        border: 1px solid black;
    }
    </style>
<body>
    <br>
    <br>
    <br>
    <form action="" method="post" name="asdf" id="asdf">
        <textarea name="source" id="asdf" cols="30" rows="10"  style="overflow:auto;resize:none" 
            form="asdf" placeholder="Paste page source dari SIMASTER di sini"></textarea>
        <br>
        <input type="submit" value="Proses">
    </form>
</body>
</html>