<?php
require_once __DIR__ . '/database_functions.php';

$file = __DIR__ . '/Datos_Demograficos_Hojas_de_Vida.xlsx';

// Try running Python script ONLY if exec() function is enabled and available on the server
$disabled_functions = array_map('trim', explode(',', (string)ini_get('disable_functions')));
$exec_available = function_exists('exec') && !in_array('exec', $disabled_functions);

if ($exec_available) {
    try {
        @exec('python ' . escapeshellarg(__DIR__ . '/exportar_demograficos_excel.py'));
    } catch (Throwable $t) {
        // Python execution failed or is restricted, fallback to PHP generation below
    }
}

// If python generated a fresh file in the last 2 minutes, serve it directly
if (file_exists($file) && (time() - filemtime($file) < 120)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Datos_Demograficos_Hojas_de_Vida.xlsx"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// Pure PHP Fallback: Generate demographic report directly without shell execution or Python
generate_demographics_excel_php();

function generate_demographics_excel_php() {
    $conn = get_db_connection();
    if (!$conn) {
        die("Error: No se pudo conectar a la base de datos.");
    }

    $str = function($v) {
        return $v !== null ? trim((string)$v) : '';
    };

    // 1. Gender Map
    $genero_map = [];
    $res_gen = $conn->query("SELECT numero_documento, genero FROM productores_panaca WHERE genero IS NOT NULL");
    if ($res_gen) {
        while ($r = $res_gen->fetch_assoc()) {
            $doc = $str($r['numero_documento']);
            $gen = $str($r['genero']);
            if ($doc && $gen) {
                $genero_map[$doc] = $gen;
            }
        }
    }

    // 2. Education Map
    $edu_map = [];
    $res_edu = $conn->query("SELECT persona_id, nivel_educacion, titulo, titulo_obtenido FROM persona_educacion");
    if ($res_edu) {
        while ($r = $res_edu->fetch_assoc()) {
            $pid = $str($r['persona_id']);
            $nivel = $str($r['nivel_educacion']);
            $tit = $str($r['titulo']);
            $tit_obt = $str($r['titulo_obtenido']);
            $t_final = $tit_obt ?: $tit;

            if (!isset($edu_map[$pid])) {
                $edu_map[$pid] = ['niveles' => [], 'titulos' => []];
            }
            if ($nivel && !in_array($nivel, $edu_map[$pid]['niveles'])) {
                $edu_map[$pid]['niveles'][] = $nivel;
            }
            if ($t_final && !in_array($t_final, $edu_map[$pid]['titulos'])) {
                $edu_map[$pid]['titulos'][] = $t_final;
            }
        }
    }

    // 3. Main Query
    $sql = "SELECT p.id, p.nombre, p.tipo_documento, p.documento, p.fecha_nacimiento, 
                   p.telefono, p.email, p.vereda, p.municipio_residencia, p.departamento_residencia,
                   p.municipio_nacimiento, p.departamento_nacimiento, p.pais_nacimiento,
                   p.descripcion, p.ruta_foto, p.ruta_cedula,
                   (SELECT COUNT(*) FROM persona_educacion WHERE persona_id = p.id) as count_edu,
                   (SELECT COUNT(*) FROM persona_educacion WHERE persona_id = p.id AND ruta_soporte IS NOT NULL AND CHAR_LENGTH(TRIM(ruta_soporte)) > 0) as count_edu_soporte,
                   (SELECT COUNT(*) FROM persona_experiencia WHERE persona_id = p.id) as count_exp,
                   (SELECT COUNT(*) FROM persona_experiencia WHERE persona_id = p.id AND ruta_soporte IS NOT NULL AND CHAR_LENGTH(TRIM(ruta_soporte)) > 0) as count_exp_soporte,
                   (SELECT COUNT(*) FROM persona_referencia WHERE persona_id = p.id) as count_ref
            FROM persona_datos_personales p
            ORDER BY p.id ASC";

    $res_main = $conn->query($sql);
    if (!$res_main) {
        die("Error al consultar datos: " . $conn->error);
    }

    $fem_names = array_flip(['ADELAIDA', 'ADRIANA', 'ALCIRA', 'ALEXANDRA', 'ANA', 'ANGELICA', 'ANGIE', 'BALBINA', 'BELLANIRE', 'BLANCA', 'CAROLINA', 'CATALINA', 'CLARIBEL', 'CLAUDIA', 'DANA', 'DEICY', 'DEISY', 'DEYSI', 'DIANA', 'DINA', 'DIVIA', 'DORA', 'DORIS', 'ELENA', 'ELSY', 'ENELIA', 'FLOR', 'FRANCY', 'GINNA', 'GLORIA', 'HERCILIA', 'HERMELINDA', 'JANETH', 'JASBLEYDI', 'JEIMMY', 'JENY', 'JESSICA', 'JHAZBLEIDY', 'JHURI', 'JOHANA', 'JOHANNA', 'JUANA', 'JUDITH', 'KAREN', 'KATERIN', 'LEIDY', 'LENI', 'LESLI', 'LICETH', 'LIDA', 'LIGIA', 'LILIANA', 'LINA', 'LIZETH', 'LUCELI', 'LUZ', 'MADELEY', 'MAIRA', 'MARIA', 'MARIANA', 'MARIBEL', 'MARILYN', 'MARISOL', 'MARLEN', 'MARTHA', 'MARY', 'MAYERLY', 'MELISA', 'MERY', 'MIREYA', 'MONICA', 'NANCY', 'NATALIA', 'NAYIBE', 'NEIDY', 'NICOL', 'NIDIA', 'NINFA', 'NOHORA', 'NUBIA', 'OBDULIA', 'PATRICIA', 'ROSARIO', 'SANDRA', 'SONIA', 'VIVIAN', 'VIVIANA', 'YAMILE', 'YANETH', 'YANI', 'YAQUELIN', 'YAREIDY', 'YEINY', 'YENI', 'YENIFER', 'YENNY', 'YENSI', 'YINA', 'YINETH', 'YOLIMA', 'YUDY', 'YULIANA', 'YURY']);

    $masc_names = array_flip(['ADRIANO', 'ALBEIRO', 'ALBINO', 'ALEJANDRO', 'ALEX', 'ALFREDO', 'ANDRES', 'AUDER', 'BRAYAN', 'CARLOS', 'CRISTIAN', 'CESAR', 'DAIRO', 'DANIEL', 'DEIVER', 'DIBER', 'DIEGO', 'DILAN', 'DUVAN', 'EDGAR', 'EMILIANO', 'ERASMO', 'FAVIO', 'FERNANDO', 'GERARDO', 'GERMAN', 'GUSTAVO', 'HARRISON', 'HEINER', 'HELMER', 'HEYDER', 'IVAN', 'JAIBER', 'JAIDER', 'JAVIER', 'JEISSON', 'JOHN', 'JOSE', 'JUAN', 'JULIO', 'KEVIN', 'LUIS', 'MAICOL', 'MANUEL', 'MAURICIO', 'MIGUEL', 'MILLER', 'NICOLAS', 'NILSON', 'NOLBERTO', 'OMAR', 'OSCAR', 'PARMENIO', 'REINALDO', 'RICARDO', 'SAMIR', 'SERGIO', 'WILLIAM', 'WILMER', 'YEISON', 'YESID', 'EDUARDO', 'OLIVERIO']);

    $rows_data = [];
    $today = new DateTime();
    $idx = 1;

    while ($r = $res_main->fetch_assoc()) {
        $pid = $str($r['id']);
        $nombre = $str($r['nombre']);
        $tipo_doc = $str($r['tipo_documento']);
        $doc = $str($r['documento']);
        $fn_str = $str($r['fecha_nacimiento']);
        $tel = $str($r['telefono']);
        $email = $str($r['email']);
        $vereda = $str($r['vereda']) ?: 'Sin especificación';
        $muni_res = $str($r['municipio_residencia']) ?: 'Sumapaz';
        $dept_res = $str($r['departamento_residencia']) ?: 'Bogotá D.C.';
        $descripcion = $str($r['descripcion']);
        $ruta_foto = $str($r['ruta_foto']);
        $ruta_cedula = $str($r['ruta_cedula']);
        $count_edu = (int)$r['count_edu'];
        $count_edu_soporte = (int)$r['count_edu_soporte'];
        $count_exp = (int)$r['count_exp'];
        $count_exp_soporte = (int)$r['count_exp_soporte'];
        $count_ref = (int)$r['count_ref'];

        $age = '';
        $fn_formatted = '';
        if ($fn_str && $fn_str !== '0000-00-00' && strlen($fn_str) >= 10) {
            try {
                $fn_dt = new DateTime(substr($fn_str, 0, 10));
                $fn_formatted = $fn_dt->format('Y-m-d');
                $diff = $today->diff($fn_dt);
                $age_val = $diff->y;
                if ($age_val >= 0 && $age_val <= 120) {
                    $age = $age_val;
                }
            } catch (Exception $e) {
                $fn_formatted = $fn_str;
            }
        }

        $gen = $genero_map[$doc] ?? '';
        if (!$gen) {
            $norm = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre));
            $words = preg_split('/\s+/', $norm);
            $gen = 'No especificado';
            foreach ($words as $w) {
                if (isset($fem_names[$w]) || strpos($w, 'MARIA') !== false) {
                    $gen = 'Femenino';
                    break;
                } elseif (isset($masc_names[$w])) {
                    $gen = 'Masculino';
                    break;
                }
            }
        }

        $ed_info = $edu_map[$pid] ?? ['niveles' => [], 'titulos' => []];
        $nivel_edu = !empty($ed_info['niveles']) ? implode(', ', $ed_info['niveles']) : 'Sin registro';
        $titulo_edu = !empty($ed_info['titulos']) ? implode(', ', $ed_info['titulos']) : 'N/A';

        $score = 0;
        $faltantes = [];
        if ($nombre) $score += 5;
        if ($doc && $tipo_doc) $score += 5;
        if ($fn_str) $score += 5;
        if ($tel && $email) $score += 5;
        if ($vereda) $score += 5;

        if ($descripcion) $score += 10; else $faltantes[] = "Perfil profesional";
        if ($ruta_foto) $score += 10; else $faltantes[] = "Foto de perfil";
        if ($ruta_cedula) $score += 15; else $faltantes[] = "Documento cédula";

        if ($count_edu > 0) {
            $score += 10;
            if ($count_edu_soporte > 0) $score += 10; else $faltantes[] = "Soporte educativo";
        } else {
            $faltantes[] = "Estudios académicos";
        }

        if ($count_exp > 0) {
            $score += 5;
            if ($count_exp_soporte > 0) $score += 5; else $faltantes[] = "Soporte laboral";
        } else {
            $faltantes[] = "Experiencia laboral";
        }

        if ($count_ref > 0) $score += 10; else $faltantes[] = "Referencias";

        $completitud = min(100, $score);
        $lo_faltante = !empty($faltantes) ? implode(', ', $faltantes) : 'Ninguno';

        $rows_data[] = [
            'num' => $idx++,
            'id' => $pid,
            'nombre' => $nombre,
            'tipo_doc' => $tipo_doc,
            'doc' => $doc,
            'completitud' => $completitud,
            'lo_faltante' => $lo_faltante,
            'fecha_nacimiento' => $fn_formatted,
            'edad' => $age,
            'sexo' => $gen,
            'nivel_educativo' => $nivel_edu,
            'titulo_obtenido' => $titulo_edu,
            'vereda' => $vereda,
            'muni_residencia' => $muni_res,
            'dept_residencia' => $dept_res,
            'telefono' => $tel,
            'email' => $email
        ];
    }

    $total_persons = count($rows_data);
    $ages_list = array_filter(array_column($rows_data, 'edad'), 'is_numeric');
    $avg_age = count($ages_list) > 0 ? round(array_sum($ages_list) / count($ages_list), 1) : 0;

    $gender_counts = ['Femenino' => 0, 'Masculino' => 0, 'No especificado' => 0];
    $vereda_counts = [];
    $edu_counts = [];
    $age_groups = ['< 18 años' => 0, '18 - 29 años (Jóvenes)' => 0, '30 - 49 años' => 0, '50 - 64 años' => 0, '65+ años' => 0];

    foreach ($rows_data as $d) {
        $g = $d['sexo'];
        $gender_counts[$g] = ($gender_counts[$g] ?? 0) + 1;

        $v = $d['vereda'];
        $vereda_counts[$v] = ($vereda_counts[$v] ?? 0) + 1;

        $ne = $d['nivel_educativo'];
        $edu_counts[$ne] = ($edu_counts[$ne] ?? 0) + 1;

        $a = $d['edad'];
        if (is_numeric($a)) {
            if ($a < 18) $age_groups['< 18 años']++;
            elseif ($a <= 29) $age_groups['18 - 29 años (Jóvenes)']++;
            elseif ($a <= 49) $age_groups['30 - 49 años']++;
            elseif ($a <= 64) $age_groups['50 - 64 años']++;
            else $age_groups['65+ años']++;
        }
    }
    arsort($vereda_counts);
    arsort($edu_counts);

    $e = function($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES | ENT_XML1, 'UTF-8');
    };

    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="Datos_Demograficos_Hojas_de_Vida.xls"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    ?>
<Workbook xmlns="urn:schemas-microsoft-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Segoe UI" x:Family="Swiss" ss:Size="10" ss:Color="#333333"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="Title">
   <Font ss:FontName="Segoe UI" ss:Size="16" ss:Bold="1" ss:Color="#1F4E79"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="Subtitle">
   <Font ss:FontName="Segoe UI" ss:Size="11" ss:Italic="1" ss:Color="#595959"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="Section">
   <Font ss:FontName="Segoe UI" ss:Size="13" ss:Bold="1" ss:Color="#1F4E79"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="Header">
   <Font ss:FontName="Segoe UI" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#1F4E79" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="DataLeft">
   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#333333"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="DataCenter">
   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#333333"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="ZebraLeft">
   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#333333"/>
   <Interior ss:Color="#F2F4F7" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="ZebraCenter">
   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#333333"/>
   <Interior ss:Color="#F2F4F7" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="CardTitle">
   <Font ss:FontName="Segoe UI" ss:Size="9" ss:Bold="1" ss:Color="#595959"/>
   <Interior ss:Color="#E9EEF4" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1F4E79"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="CardValue">
   <Font ss:FontName="Segoe UI" ss:Size="16" ss:Bold="1" ss:Color="#1F4E79"/>
   <Interior ss:Color="#E9EEF4" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1F4E79"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
  <Style ss:ID="CardSub">
   <Font ss:FontName="Segoe UI" ss:Size="8" ss:Italic="1" ss:Color="#7F7F7F"/>
   <Interior ss:Color="#E9EEF4" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1F4E79"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>
   </Borders>
  </Style>
 </Styles>

 <!-- SHEET 1: Data Table -->
 <Worksheet ss:Name="Hojas de Vida - Demografía">
  <Table ss:ExpandedColumnCount="17" ss:ExpandedRowCount="<?php echo count($rows_data) + 10; ?>" x:FullColumns="1" x:FullRows="1">
   <Column ss:Width="40"/> <!-- N° -->
   <Column ss:Width="45"/> <!-- ID -->
   <Column ss:Width="200"/> <!-- Nombre -->
   <Column ss:Width="130"/> <!-- Tipo Doc -->
   <Column ss:Width="110"/> <!-- Documento -->
   <Column ss:Width="90"/> <!-- Completitud -->
   <Column ss:Width="220"/> <!-- Lo Faltante -->
   <Column ss:Width="100"/> <!-- Fecha Nacimiento -->
   <Column ss:Width="50"/> <!-- Edad -->
   <Column ss:Width="100"/> <!-- Sexo -->
   <Column ss:Width="170"/> <!-- Nivel Educativo -->
   <Column ss:Width="230"/> <!-- Título Obtenido -->
   <Column ss:Width="140"/> <!-- Vereda -->
   <Column ss:Width="120"/> <!-- Muni Res -->
   <Column ss:Width="120"/> <!-- Dept Res -->
   <Column ss:Width="100"/> <!-- Teléfono -->
   <Column ss:Width="200"/> <!-- Email -->

   <Row ss:Height="28">
    <Cell ss:MergeAcross="16" ss:StyleID="Title"><Data ss:Type="String">REPORTE DE DATOS DEMOGRÁFICOS - HOJAS DE VIDA</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:MergeAcross="16" ss:StyleID="Subtitle"><Data ss:Type="String">Alcaldía Local de Sumapaz | Generado el <?php echo date('Y-m-d'); ?> | Total Registros: <?php echo $total_persons; ?></Data></Cell>
   </Row>
   <Row ss:Height="10"/>

   <!-- Table Headers -->
   <Row ss:Height="28">
    <Cell ss:StyleID="Header"><Data ss:Type="String">N°</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">ID</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nombre Completo</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Tipo Doc.</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Número Documento</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Completitud (%)</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Lo Faltante</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha Nacimiento</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Edad</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Sexo / Género</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nivel Educativo</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Título / Formación Obtenida</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Vereda</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Municipio Residencia</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Departamento Residencia</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Teléfono</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Correo Electrónico</Data></Cell>
   </Row>

   <!-- Data Rows -->
   <?php foreach ($rows_data as $i => $d): 
       $is_even = ($i % 2 == 1);
       $style_c = $is_even ? 'ZebraCenter' : 'DataCenter';
       $style_l = $is_even ? 'ZebraLeft' : 'DataLeft';
   ?>
   <Row ss:Height="22">
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="Number"><?php echo $d['num']; ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="<?php echo is_numeric($d['id']) ? 'Number' : 'String'; ?>"><?php echo $e($d['id']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['nombre']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $e($d['tipo_doc']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $e($d['doc']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $d['completitud']; ?>%</Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['lo_faltante']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $e($d['fecha_nacimiento']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="<?php echo is_numeric($d['edad']) ? 'Number' : 'String'; ?>"><?php echo $e($d['edad']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $e($d['sexo']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['nivel_educativo']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['titulo_obtenido']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['vereda']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['muni_residencia']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['dept_residencia']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_c; ?>"><Data ss:Type="String"><?php echo $e($d['telefono']); ?></Data></Cell>
    <Cell ss:StyleID="<?php echo $style_l; ?>"><Data ss:Type="String"><?php echo $e($d['email']); ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-microsoft-com:office:excel">
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>

 <!-- SHEET 2: Summary Dashboard -->
 <Worksheet ss:Name="Resumen Demográfico">
  <Table ss:ExpandedColumnCount="12" ss:ExpandedRowCount="100" x:FullColumns="1" x:FullRows="1">
   <Column ss:Width="160"/>
   <Column ss:Width="70"/>
   <Column ss:Width="60"/>
   <Column ss:Width="30"/>
   <Column ss:Width="200"/>
   <Column ss:Width="70"/>
   <Column ss:Width="60"/>
   <Column ss:Width="30"/>
   <Column ss:Width="160"/>
   <Column ss:Width="70"/>
   <Column ss:Width="60"/>

   <Row ss:Height="28">
    <Cell ss:MergeAcross="6" ss:StyleID="Title"><Data ss:Type="String">DASHBOARD DEMOGRÁFICO Y ESTADÍSTICAS</Data></Cell>
   </Row>
   <Row ss:Height="10"/>

   <!-- KPI Cards Row 1: Titles -->
   <Row ss:Height="20">
    <Cell ss:MergeAcross="1" ss:StyleID="CardTitle"><Data ss:Type="String">Total Hojas de Vida</Data></Cell>
    <Cell ss:Index="4" ss:MergeAcross="1" ss:StyleID="CardTitle"><Data ss:Type="String">Promedio de Edad</Data></Cell>
    <Cell ss:Index="7" ss:MergeAcross="1" ss:StyleID="CardTitle"><Data ss:Type="String">Mujeres</Data></Cell>
    <Cell ss:Index="10" ss:MergeAcross="1" ss:StyleID="CardTitle"><Data ss:Type="String">Hombres</Data></Cell>
   </Row>
   <!-- KPI Cards Row 2: Values -->
   <?php
       $fem_pct = $total_persons > 0 ? round(($gender_counts['Femenino'] / $total_persons) * 100, 1) : 0;
       $masc_pct = $total_persons > 0 ? round(($gender_counts['Masculino'] / $total_persons) * 100, 1) : 0;
   ?>
   <Row ss:Height="30">
    <Cell ss:MergeAcross="1" ss:StyleID="CardValue"><Data ss:Type="Number"><?php echo $total_persons; ?></Data></Cell>
    <Cell ss:Index="4" ss:MergeAcross="1" ss:StyleID="CardValue"><Data ss:Type="String"><?php echo $avg_age; ?> años</Data></Cell>
    <Cell ss:Index="7" ss:MergeAcross="1" ss:StyleID="CardValue"><Data ss:Type="String"><?php echo $gender_counts['Femenino']; ?> (<?php echo $fem_pct; ?>%)</Data></Cell>
    <Cell ss:Index="10" ss:MergeAcross="1" ss:StyleID="CardValue"><Data ss:Type="String"><?php echo $gender_counts['Masculino']; ?> (<?php echo $masc_pct; ?>%)</Data></Cell>
   </Row>
   <!-- KPI Cards Row 3: Subtitles -->
   <Row ss:Height="18">
    <Cell ss:MergeAcross="1" ss:StyleID="CardSub"><Data ss:Type="String">Personas registradas</Data></Cell>
    <Cell ss:Index="4" ss:MergeAcross="1" ss:StyleID="CardSub"><Data ss:Type="String">Rango etario población</Data></Cell>
    <Cell ss:Index="7" ss:MergeAcross="1" ss:StyleID="CardSub"><Data ss:Type="String">Población femenina</Data></Cell>
    <Cell ss:Index="10" ss:MergeAcross="1" ss:StyleID="CardSub"><Data ss:Type="String">Población masculina</Data></Cell>
   </Row>

   <Row ss:Height="20"/>

   <!-- Tables Headers -->
   <Row ss:Height="24">
    <Cell ss:MergeAcross="2" ss:StyleID="Section"><Data ss:Type="String">Distribución por Vereda</Data></Cell>
    <Cell ss:Index="5" ss:MergeAcross="2" ss:StyleID="Section"><Data ss:Type="String">Distribución por Nivel Educativo</Data></Cell>
    <Cell ss:Index="9" ss:MergeAcross="2" ss:StyleID="Section"><Data ss:Type="String">Distribución por Rango Etario</Data></Cell>
   </Row>

   <Row ss:Height="24">
    <Cell ss:StyleID="Header"><Data ss:Type="String">Vereda</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Cantidad</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">%</Data></Cell>

    <Cell ss:Index="5" ss:StyleID="Header"><Data ss:Type="String">Nivel Educativo</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Cantidad</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">%</Data></Cell>

    <Cell ss:Index="9" ss:StyleID="Header"><Data ss:Type="String">Rango Etario</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Cantidad</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">%</Data></Cell>
   </Row>

   <!-- Tables Rows -->
   <?php
       $vereda_keys = array_keys($vereda_counts);
       $edu_keys = array_keys($edu_counts);
       $age_keys = array_keys($age_groups);

       $max_rows = max(count($vereda_keys), count($edu_keys), count($age_keys));
       for ($r = 0; $r < $max_rows; $r++):
           $v_name = $vereda_keys[$r] ?? null;
           $e_name = $edu_keys[$r] ?? null;
           $a_name = $age_keys[$r] ?? null;
   ?>
   <Row ss:Height="20">
    <!-- Vereda Column -->
    <?php if ($v_name !== null): 
        $v_cnt = $vereda_counts[$v_name];
        $v_pct = $total_persons > 0 ? round(($v_cnt / $total_persons) * 100, 1) : 0;
    ?>
     <Cell ss:StyleID="DataLeft"><Data ss:Type="String"><?php echo $e($v_name); ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="Number"><?php echo $v_cnt; ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="String"><?php echo $v_pct; ?>%</Data></Cell>
    <?php else: ?>
     <Cell/><Cell/><Cell/>
    <?php endif; ?>

    <Cell/> <!-- Spacer -->

    <!-- Edu Column -->
    <?php if ($e_name !== null): 
        $e_cnt = $edu_counts[$e_name];
        $e_pct = $total_persons > 0 ? round(($e_cnt / $total_persons) * 100, 1) : 0;
    ?>
     <Cell ss:StyleID="DataLeft"><Data ss:Type="String"><?php echo $e($e_name); ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="Number"><?php echo $e_cnt; ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="String"><?php echo $e_pct; ?>%</Data></Cell>
    <?php else: ?>
     <Cell/><Cell/><Cell/>
    <?php endif; ?>

    <Cell/> <!-- Spacer -->

    <!-- Age Column -->
    <?php if ($a_name !== null): 
        $a_cnt = $age_groups[$a_name];
        $a_pct = $total_persons > 0 ? round(($a_cnt / $total_persons) * 100, 1) : 0;
    ?>
     <Cell ss:StyleID="DataLeft"><Data ss:Type="String"><?php echo $e($a_name); ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="Number"><?php echo $a_cnt; ?></Data></Cell>
     <Cell ss:StyleID="DataCenter"><Data ss:Type="String"><?php echo $a_pct; ?>%</Data></Cell>
    <?php else: ?>
     <Cell/><Cell/><Cell/>
    <?php endif; ?>
   </Row>
   <?php endfor; ?>

  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-microsoft-com:office:excel">
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
<?php
    exit;
}
?>
