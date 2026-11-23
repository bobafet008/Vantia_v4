<?php

class PdfReporteDiario
{
    private $fecha;
    private $datos;
    private $adminNombre;
    private $tipoReporte;

    private $pageWidth = 595.28;
    private $pageHeight = 841.89;
    private $marginLeft = 40.0;
    private $marginRight = 40.0;
    private $marginTop = 40.0;
    private $marginBottom = 45.0;
    private $contentWidth;

    private $pages = [];
    private $currentPage = 0;
    private $curY = 0;

    public function __construct(string $fecha, array $datos = [], string $adminNombre = 'Administrador', string $tipoReporte = 'diario')
    {
        $this->fecha = $fecha;
        $this->datos = $datos;
        $this->adminNombre = $adminNombre;
        $this->tipoReporte = $tipoReporte;
        $this->contentWidth = $this->pageWidth - $this->marginLeft - $this->marginRight;
    }

    private function sanitizeText($text): string
    {
        if ($text === null) {
            return '';
        }
        $str = (string) $text;

        $emojiPattern = '/[\x{1F300}-\x{1FAD6}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}\x{200D}]/u';
        $str = preg_replace($emojiPattern, '', $str);

        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($str, 'Windows-1252', 'UTF-8');
            if ($converted !== false) {
                $str = $converted;
            }
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $str);
            if ($converted !== false) {
                $str = $converted;
            }
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $str);
    }

    private function addPage(): void
    {
        $this->currentPage++;
        $this->pages[$this->currentPage] = '';
        $this->curY = $this->pageHeight - $this->marginTop;
        $this->drawPageHeader();
    }

    private function checkPageBreak(float $neededHeight): void
    {
        if (($this->curY - $neededHeight) < $this->marginBottom) {
            $this->addPage();
        }
    }

    private function rawAppend(string $code): void
    {
        if ($this->currentPage === 0) {
            $this->addPage();
        }
        $this->pages[$this->currentPage] .= $code . "\n";
    }

    private function drawRect(float $x, float $y, float $w, float $h, string $style = 'F', array $fill = [1, 1, 1], array $stroke = [0.8, 0.8, 0.8], float $lineWidth = 0.5): void
    {
        $code = sprintf("%.2f w\n", $lineWidth);
        if ($style === 'F' || $style === 'FD' || $style === 'DF') {
            $code .= sprintf("%.3f %.3f %.3f rg\n", $fill[0], $fill[1], $fill[2]);
        }
        if ($style === 'D' || $style === 'FD' || $style === 'DF') {
            $code .= sprintf("%.3f %.3f %.3f RG\n", $stroke[0], $stroke[1], $stroke[2]);
        }
        $code .= sprintf("%.2f %.2f %.2f %.2f re\n", $x, $y, $w, $h);
        if ($style === 'F') {
            $code .= "f\n";
        } elseif ($style === 'D') {
            $code .= "S\n";
        } else {
            $code .= "B\n";
        }
        $this->rawAppend($code);
    }

    private function drawLine(float $x1, float $y1, float $x2, float $y2, array $color = [0.8, 0.8, 0.8], float $width = 0.5): void
    {
        $code = sprintf("%.2f w\n", $width);
        $code .= sprintf("%.3f %.3f %.3f RG\n", $color[0], $color[1], $color[2]);
        $code .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $x1, $y1, $x2, $y2);
        $this->rawAppend($code);
    }

    private function drawText(string $text, float $x, float $y, float $size = 10, string $font = 'F1', array $color = [0.1, 0.1, 0.1]): void
    {
        $sanitized = $this->sanitizeText($text);
        $code = sprintf("%.3f %.3f %.3f rg\n", $color[0], $color[1], $color[2]);
        $code .= sprintf("BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n", $font, $size, $x, $y, $sanitized);
        $this->rawAppend($code);
    }

    private function drawPageHeader(): void
    {

        $this->drawRect($this->marginLeft, $this->curY - 3, $this->contentWidth, 3, 'F', [0.118, 0.251, 0.686]);

        $this->drawText("VANTIA", $this->marginLeft, $this->curY - 16, 11, 'F2', [0.118, 0.251, 0.686]);
        $this->drawText("|  Plataforma de Empleabilidad Juvenil Verificada", $this->marginLeft + 48, $this->curY - 16, 8.5, 'F1', [0.392, 0.455, 0.545]);

        $fechaTexto = date('d/m/Y', strtotime($this->fecha));
        $titHeader = ($this->tipoReporte === 'admin') ? "AUDITORIA DE ACCIONES DEL ADMINISTRADOR" : "REPORTE DIARIO DE AUDITORIA";
        $this->drawText("$titHeader  -  $fechaTexto", $this->marginLeft + 230, $this->curY - 16, 8.5, 'F2', [0.118, 0.251, 0.686]);

        $this->drawLine($this->marginLeft, $this->curY - 22, $this->marginLeft + $this->contentWidth, $this->curY - 22, [0.85, 0.88, 0.92], 0.6);

        $this->curY -= 32;
    }

    private function drawPageFooters(): void
    {
        for ($p = 1; $p <= $this->currentPage; $p++) {
            $y = $this->marginBottom - 15;
            $lineCode = sprintf("0.5 w\n0.85 0.88 0.92 RG\n%.2f %.2f m %.2f %.2f l S\n", $this->marginLeft, $y + 12, $this->marginLeft + $this->contentWidth, $y + 12);
            $this->pages[$p] .= $lineCode;

            $txtIzquierda = $this->sanitizeText("Vantia Sistema Central | Generado por " . $this->adminNombre . " el " . date('d/m/Y H:i:s'));
            $txtDerecha = $this->sanitizeText("Pagina $p de " . $this->currentPage);

            $this->pages[$p] .= sprintf("0.45 0.50 0.58 rg\nBT /F1 8 Tf %.2f %.2f Td (%s) Tj ET\n", $this->marginLeft, $y, $txtIzquierda);
            $this->pages[$p] .= sprintf("0.45 0.50 0.58 rg\nBT /F2 8 Tf %.2f %.2f Td (%s) Tj ET\n", $this->marginLeft + $this->contentWidth - 65, $y, $txtDerecha);
        }
    }

    private function buildDocument(): void
    {
        $this->addPage();

        $esAdmin = ($this->tipoReporte === 'admin');

        $this->drawRect($this->marginLeft, $this->curY - 56, $this->contentWidth, 56, 'FD', [0.97, 0.98, 1.0], [0.80, 0.86, 0.96], 1);

        $tituloPrincipal = $esAdmin
            ? "INFORME OFICIAL DE AUDITORIA: ACCIONES DEL ADMINISTRADOR"
            : "INFORME OFICIAL DE AUDITORIA Y ACCIONES DEL ADMINISTRADOR";

        $this->drawText($tituloPrincipal, $this->marginLeft + 16, $this->curY - 20, 13, 'F2', [0.08, 0.18, 0.45]);

        $timestamp = strtotime($this->fecha);
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $diaNom = $dias[(int) date('w', $timestamp)];
        $diaNum = date('j', $timestamp);
        $mesNom = $meses[(int) date('n', $timestamp)];
        $anio = date('Y', $timestamp);
        $fechaLarga = "$diaNom, $diaNum de $mesNom de $anio";

        $subtitulo = $esAdmin
            ? "Bitacora completa de intervenciones, resoluciones y modificaciones de administracion"
            : "Registro diario de cambios, auditoria de administradores y control de seguridad";

        $this->drawText("Jornada analizada: $fechaLarga (Fecha: " . date('d/m/Y', $timestamp) . ") | $subtitulo", $this->marginLeft + 16, $this->curY - 35, 8.5, 'F1', [0.2, 0.3, 0.4]);
        $this->drawText("Responsable: " . $this->adminNombre . " | Auditoria Administrativa Vantia", $this->marginLeft + 16, $this->curY - 48, 8.5, 'F3', [0.4, 0.5, 0.6]);

        $this->curY -= 70;

        $nuevos = count($this->datos['nuevosUsuarios'] ?? []);
        $baneos = count($this->datos['baneos'] ?? []);
        $desbaneos = count($this->datos['desbaneos'] ?? []);
        $adminLogs = count($this->datos['registrosAdmin'] ?? []);
        $soporteCount = count($this->datos['mensajesSoporte'] ?? []);
        $totalEventos = $nuevos + $baneos + $desbaneos + $adminLogs + $soporteCount;

        if ($esAdmin) {
            $cntVerif = 0;
            $cntBaneos = 0;
            $cntDesbaneos = 0;
            $cntOtros = 0;
            foreach ($this->datos['registrosAdmin'] ?? [] as $ra) {
                $te = $ra['tipo_evento'] ?? '';
                if ($te === 'verificacion') $cntVerif++;
                elseif ($te === 'baneo') $cntBaneos++;
                elseif ($te === 'desbaneo') $cntDesbaneos++;
                else $cntOtros++;
            }

            $kpis = [
                ['Acciones Admin', "$adminLogs", [0.12, 0.25, 0.69]],
                ['Atencion Cliente', "$soporteCount", [0.05, 0.55, 0.70]],
                ['Verificaciones', "$cntVerif", [0.15, 0.35, 0.85]],
                ['Baneos / Sanciones', "$cntBaneos", [0.85, 0.15, 0.15]],
                ['Desbaneos', "$cntDesbaneos", [0.02, 0.58, 0.41]],
            ];
        } else {
            $kpis = [
                ['Acciones Admin', "$adminLogs", [0.12, 0.25, 0.69]],
                ['Atencion Cliente', "$soporteCount", [0.05, 0.55, 0.70]],
                ['Baneos / Sanciones', "$baneos", [0.85, 0.15, 0.15]],
                ['Desbaneos', "$desbaneos", [0.02, 0.58, 0.41]],
                ['Nuevos Usuarios', "+$nuevos", [0.06, 0.55, 0.35]],
            ];
        }

        $boxW = ($this->contentWidth - (4 * 10)) / 5;
        $boxH = 40;
        foreach ($kpis as $idx => $kpi) {
            $bx = $this->marginLeft + ($idx * ($boxW + 10));
            $this->drawRect($bx, $this->curY - $boxH, $boxW, $boxH, 'FD', [1, 1, 1], [0.85, 0.88, 0.92], 0.8);
            $this->drawRect($bx, $this->curY - 2.5, $boxW, 2.5, 'F', $kpi[2]);

            $this->drawText($kpi[1], $bx + 8, $this->curY - 20, 13, 'F2', $kpi[2]);
            $this->drawText($kpi[0], $bx + 8, $this->curY - 32, 6.8, 'F1', [0.4, 0.45, 0.5]);
        }

        $this->curY -= ($boxH + 20);

        $this->renderSeccionLogsAdmin($this->datos['registrosAdmin'] ?? [], "1");
        $this->renderSeccionSoporte($this->datos['mensajesSoporte'] ?? [], "2");
        $this->renderSeccionBaneos($this->datos['baneos'] ?? [], "3");
        $this->renderSeccionDesbaneos($this->datos['desbaneos'] ?? [], "4");
        $this->renderSeccionNuevosUsuarios($this->datos['nuevosUsuarios'] ?? [], "5");
    }

    private function renderTituloSeccion(string $numero, string $titulo, int $cantidad): void
    {
        $this->checkPageBreak(38);
        $this->drawRect($this->marginLeft, $this->curY - 22, $this->contentWidth, 22, 'F', [0.93, 0.95, 0.98]);
        $this->drawRect($this->marginLeft, $this->curY - 22, 3.5, 22, 'F', [0.118, 0.251, 0.686]);

        $texto = "$numero. " . strtoupper($titulo);
        $this->drawText($texto, $this->marginLeft + 10, $this->curY - 15, 9.5, 'F2', [0.08, 0.18, 0.45]);
        $this->drawText("$cantidad registro(s) en la fecha", $this->marginLeft + $this->contentWidth - 110, $this->curY - 15, 8, 'F1', [0.4, 0.45, 0.55]);

        $this->curY -= 26;
    }

    private function renderHeaderTabla(array $cols, array $widths): void
    {
        $this->checkPageBreak(18);
        $this->drawRect($this->marginLeft, $this->curY - 16, $this->contentWidth, 16, 'F', [0.15, 0.23, 0.35]);

        $curX = $this->marginLeft;
        foreach ($cols as $i => $col) {
            $w = $widths[$i];
            $this->drawText($col, $curX + 6, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
            $curX += $w;
        }
        $this->curY -= 16;
    }

    private function renderFilaTabla(array $vals, array $widths, bool $alt): void
    {
        $this->checkPageBreak(17);
        $h = 16.5;
        $bg = $alt ? [0.97, 0.98, 0.99] : [1, 1, 1];
        $this->drawRect($this->marginLeft, $this->curY - $h, $this->contentWidth, $h, 'F', $bg);
        $this->drawLine($this->marginLeft, $this->curY - $h, $this->marginLeft + $this->contentWidth, $this->curY - $h, [0.88, 0.90, 0.93], 0.4);

        $curX = $this->marginLeft;
        foreach ($vals as $i => $val) {
            $w = $widths[$i];

            $maxChars = (int) ($w / 5.2);
            $texto = (mb_strlen($val) > $maxChars) ? mb_substr($val, 0, $maxChars - 2) . '..' : $val;
            $this->drawText($texto, $curX + 6, $this->curY - 11.5, 7.5, 'F1', [0.15, 0.20, 0.25]);
            $curX += $w;
        }
        $this->curY -= $h;
    }

    private function renderTablaVacia(string $mensaje): void
    {
        $this->checkPageBreak(22);
        $this->drawRect($this->marginLeft, $this->curY - 20, $this->contentWidth, 20, 'F', [0.98, 0.99, 1.0]);
        $this->drawLine($this->marginLeft, $this->curY - 20, $this->marginLeft + $this->contentWidth, $this->curY - 20, [0.90, 0.92, 0.95], 0.5);
        $this->drawText("- " . $mensaje, $this->marginLeft + 12, $this->curY - 13.5, 8, 'F3', [0.5, 0.55, 0.6]);
        $this->curY -= 26;
    }

    private function renderSeccionLogsAdmin(array $items, string $numero = "1"): void
    {
        $this->renderTituloSeccion($numero, "Auditoria y Acciones del Administrador", count($items));
        if (empty($items)) {
            $this->renderTablaVacia("No se registraron intervenciones administrativas en esta fecha.");
            return;
        }

        $this->checkPageBreak(18);
        $this->drawRect($this->marginLeft, $this->curY - 16, $this->contentWidth, 16, 'F', [0.12, 0.20, 0.35]);
        $this->drawText("Hora / Ref", $this->marginLeft + 8, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Administrador Responsable / IP", $this->marginLeft + 76, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Accion Administrativa / Descripcion Detallada", $this->marginLeft + 210, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Usuario / Entidad Afectada", $this->marginLeft + 400, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->curY -= 16;

        foreach ($items as $idx => $item) {
            $this->checkPageBreak(28);
            $h = 26.0;
            $alt = ($idx % 2 === 1);
            $bg = $alt ? [0.97, 0.98, 1.0] : [1, 1, 1];

            $this->drawRect($this->marginLeft, $this->curY - $h, $this->contentWidth, $h, 'F', $bg);
            $this->drawLine($this->marginLeft, $this->curY - $h, $this->marginLeft + $this->contentWidth, $this->curY - $h, [0.88, 0.90, 0.94], 0.5);

            $tipo = $item['tipo_evento'] ?? 'accion_admin';
            $colorBorde = [0.35, 0.40, 0.50];
            if ($tipo === 'baneo') $colorBorde = [0.85, 0.15, 0.15];
            elseif ($tipo === 'desbaneo') $colorBorde = [0.05, 0.60, 0.40];
            elseif ($tipo === 'verificacion') $colorBorde = [0.15, 0.35, 0.85];

            $this->drawRect($this->marginLeft, $this->curY - $h, 3.5, $h, 'F', $colorBorde);

            $hora = !empty($item['fecha_registro']) ? date('H:i:s', strtotime($item['fecha_registro'])) : '-';
            $idRef = "#" . ($item['id'] ?? ($idx + 1));
            $admin = $item['admin_nombre'] ?? (!empty($item['admin_id']) ? "Admin #{$item['admin_id']}" : 'Sistema Vantia');
            $ip = $item['ip_origen'] ?? '127.0.0.1';
            $tipoTexto = strtoupper(str_replace('_', ' ', $tipo));
            $titulo = $item['titulo'] ?? 'Accion Administrativa';
            $desc = $item['descripcion'] ?? '-';
            $afectado = $item['usuario_afectado'] ?? '-';

            $this->drawText($hora, $this->marginLeft + 8, $this->curY - 10, 7.8, 'F2', [0.10, 0.15, 0.25]);
            $this->drawText($idRef . " " . $tipoTexto, $this->marginLeft + 8, $this->curY - 20, 6.5, 'F2', $colorBorde);

            $adminShort = (mb_strlen($admin) > 24) ? mb_substr($admin, 0, 22) . '..' : $admin;
            $this->drawText($adminShort, $this->marginLeft + 76, $this->curY - 10, 7.5, 'F2', [0.15, 0.20, 0.30]);
            $this->drawText("IP: " . $ip, $this->marginLeft + 76, $this->curY - 20, 6.8, 'F3', [0.45, 0.50, 0.58]);

            $tituloShort = (mb_strlen($titulo) > 38) ? mb_substr($titulo, 0, 36) . '..' : $titulo;
            $this->drawText($tituloShort, $this->marginLeft + 210, $this->curY - 10, 7.8, 'F2', [0.08, 0.15, 0.35]);

            $descShort = (mb_strlen($desc) > 50) ? mb_substr($desc, 0, 48) . '..' : $desc;
            $this->drawText($descShort, $this->marginLeft + 210, $this->curY - 20, 7.0, 'F1', [0.30, 0.35, 0.42]);

            $afectadoShort = (mb_strlen($afectado) > 25) ? mb_substr($afectado, 0, 23) . '..' : $afectado;
            $this->drawText($afectadoShort, $this->marginLeft + 400, $this->curY - 14, 7.5, 'F1', [0.10, 0.15, 0.25]);

            $this->curY -= $h;
        }
        $this->curY -= 12;
    }

    private function renderSeccionSoporte(array $items, string $numero = "2"): void
    {
        $this->renderTituloSeccion($numero, "Gmails y Consultas de Atencion al Cliente", count($items));
        if (empty($items)) {
            $this->renderTablaVacia("No se recibieron mensajes o consultas de atencion al cliente en esta fecha.");
            return;
        }

        $this->checkPageBreak(18);
        $this->drawRect($this->marginLeft, $this->curY - 16, $this->contentWidth, 16, 'F', [0.08, 0.28, 0.42]);
        $this->drawText("Hora / Ref", $this->marginLeft + 8, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Remitente / Rol", $this->marginLeft + 76, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Correo Gmail de Contacto", $this->marginLeft + 190, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Asunto y Detalle de la Consulta", $this->marginLeft + 315, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->drawText("Estado", $this->marginLeft + 475, $this->curY - 11, 7.8, 'F2', [1, 1, 1]);
        $this->curY -= 16;

        foreach ($items as $idx => $item) {
            $this->checkPageBreak(28);
            $h = 26.0;
            $alt = ($idx % 2 === 1);
            $bg = $alt ? [0.97, 0.99, 1.0] : [1, 1, 1];

            $this->drawRect($this->marginLeft, $this->curY - $h, $this->contentWidth, $h, 'F', $bg);
            $this->drawLine($this->marginLeft, $this->curY - $h, $this->marginLeft + $this->contentWidth, $this->curY - $h, [0.88, 0.90, 0.94], 0.5);

            $estado = $item['estado'] ?? 'pendiente';
            $esRespondido = ($estado === 'respondido');
            $colorBorde = $esRespondido ? [0.05, 0.60, 0.40] : [0.88, 0.45, 0.05];
            $this->drawRect($this->marginLeft, $this->curY - $h, 3.5, $h, 'F', $colorBorde);

            $hora = !empty($item['creado_en']) ? date('H:i:s', strtotime($item['creado_en'])) : '-';
            $idRef = "#" . ($item['id'] ?? ($idx + 1));
            $nombre = $item['nombre'] ?? 'Usuario';
            $rol = $item['rol'] ?? 'Usuario';
            $email = $item['email'] ?? '-';
            $asunto = $item['asunto'] ?? 'Consulta';
            $mensaje = $item['mensaje'] ?? '';
            $estadoTxt = $esRespondido ? 'Respondido' : 'Pendiente';

            $this->drawText($hora, $this->marginLeft + 8, $this->curY - 10, 7.8, 'F2', [0.10, 0.15, 0.25]);
            $this->drawText($idRef . " SOPORTE", $this->marginLeft + 8, $this->curY - 20, 6.5, 'F2', [0.08, 0.28, 0.42]);

            $nombreShort = (mb_strlen($nombre) > 20) ? mb_substr($nombre, 0, 18) . '..' : $nombre;
            $this->drawText($nombreShort, $this->marginLeft + 76, $this->curY - 10, 7.5, 'F2', [0.15, 0.20, 0.30]);
            $rolShort = (mb_strlen($rol) > 22) ? mb_substr($rol, 0, 20) . '..' : $rol;
            $this->drawText($rolShort, $this->marginLeft + 76, $this->curY - 20, 6.8, 'F1', [0.45, 0.50, 0.58]);

            $emailShort = (mb_strlen($email) > 22) ? mb_substr($email, 0, 20) . '..' : $email;
            $this->drawText($emailShort, $this->marginLeft + 190, $this->curY - 14, 7.5, 'F1', [0.10, 0.15, 0.25]);

            $asuntoShort = (mb_strlen($asunto) > 30) ? mb_substr($asunto, 0, 28) . '..' : $asunto;
            $this->drawText($asuntoShort, $this->marginLeft + 315, $this->curY - 10, 7.8, 'F2', [0.08, 0.15, 0.35]);

            $limpioMsg = trim(preg_replace('/\s+/', ' ', $mensaje));
            $msgShort = (mb_strlen($limpioMsg) > 34) ? mb_substr($limpioMsg, 0, 32) . '..' : $limpioMsg;
            $this->drawText($msgShort, $this->marginLeft + 315, $this->curY - 20, 7.0, 'F1', [0.35, 0.40, 0.45]);

            $this->drawText($estadoTxt, $this->marginLeft + 475, $this->curY - 14, 7.5, 'F2', $colorBorde);

            $this->curY -= $h;
        }
        $this->curY -= 12;
    }

    private function renderSeccionBaneos(array $items, string $numero = "3"): void
    {
        $this->renderTituloSeccion($numero, "Cuentas Suspendidas y Sanciones (Baneos)", count($items));
        if (empty($items)) {
            $this->renderTablaVacia("No se registraron suspensiones o sanciones de cuentas en esta fecha.");
            return;
        }

        $cols = ['Hora', 'Entidad / Rol', 'Usuario / Entidad Afectada', 'Tipo Suspension', 'Motivo de la Sancion'];
        $widths = [45, 80, 145, 95, 150.28];
        $this->renderHeaderTabla($cols, $widths);

        foreach ($items as $idx => $item) {
            $hora = !empty($item['fecha_registro']) ? date('H:i:s', strtotime($item['fecha_registro'])) : (!empty($item['fecha']) ? date('H:i:s', strtotime($item['fecha'])) : '-');
            $rol = ucfirst($item['tipo_entidad'] ?? 'Cuenta');
            $nombre = $item['usuario_afectado'] ?? ($item['nombre'] ?? '-');
            $esTemp = !empty($item['baneado_hasta']);
            $tipoBaneo = $esTemp ? 'Temporal' : 'Permanente';
            $motivo = $item['descripcion'] ?? ($item['motivo_baneo'] ?? 'Incumplimiento de normas');

            $this->renderFilaTabla([$hora, $rol, $nombre, $tipoBaneo, $motivo], $widths, $idx % 2 === 1);
        }
        $this->curY -= 12;
    }

    private function renderSeccionDesbaneos(array $items, string $numero = "4"): void
    {
        $this->renderTituloSeccion($numero, "Historial de Desbaneos y Cuentas Reactivadas", count($items));
        if (empty($items)) {
            $this->renderTablaVacia("No se registraron desbaneos o reactivaciones en esta fecha.");
            return;
        }

        $cols = ['Hora', 'Administrador', 'Cuenta / Beneficiario', 'Detalle de Reactivacion', 'IP Origen'];
        $widths = [45, 90, 140, 175.28, 65];
        $this->renderHeaderTabla($cols, $widths);

        foreach ($items as $idx => $item) {
            $hora = !empty($item['fecha_registro']) ? date('H:i:s', strtotime($item['fecha_registro'])) : '-';
            $admin = $item['admin_nombre'] ?? 'Administrador';
            $afectado = $item['usuario_afectado'] ?? '-';
            $detalle = $item['descripcion'] ?? ($item['titulo'] ?? 'Suspension revocada');
            $ip = $item['ip_origen'] ?? '127.0.0.1';

            $this->renderFilaTabla([$hora, $admin, $afectado, $detalle, $ip], $widths, $idx % 2 === 1);
        }
        $this->curY -= 12;
    }

    private function renderSeccionNuevosUsuarios(array $items, string $numero = "5"): void
    {
        $this->renderTituloSeccion($numero, "Nuevos Usuarios Registrados", count($items));
        if (empty($items)) {
            $this->renderTablaVacia("No se registraron nuevas cuentas de usuario en esta fecha.");
            return;
        }

        $cols = ['Hora', 'Rol / Tipo', 'Nombre / Razon Social', 'Correo Electronico', 'Telefono', 'Estado'];
        $widths = [45, 75, 140, 140, 65, 50.28];
        $this->renderHeaderTabla($cols, $widths);

        foreach ($items as $idx => $item) {
            $hora = !empty($item['fecha']) ? date('H:i:s', strtotime($item['fecha'])) : '-';
            $rol = ucfirst($item['rol'] ?? 'Usuario');
            $nombre = $item['nombre'] ?? '-';
            $correo = $item['correo'] ?? '-';
            $tel = !empty($item['telefono']) ? (string)$item['telefono'] : 'No reg.';
            $estado = !empty($item['baneado']) ? 'Suspendido' : 'Activo';

            $this->renderFilaTabla([$hora, $rol, $nombre, $correo, $tel, $estado], $widths, $idx % 2 === 1);
        }
        $this->curY -= 12;
    }

    public function render(): string
    {
        $this->buildDocument();
        $this->drawPageFooters();

        $out = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $offsets = [];

        $offsets[1] = strlen($out);
        $out .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $kids = [];
        for ($i = 1; $i <= $this->currentPage; $i++) {
            $kids[] = (5 + ($i * 2) - 1) . " 0 R";
        }
        $offsets[2] = strlen($out);
        $out .= "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . $this->currentPage . " >>\nendobj\n";

        $offsets[3] = strlen($out);
        $out .= "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offsets[4] = strlen($out);
        $out .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offsets[5] = strlen($out);
        $out .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>\nendobj\n";

        $objId = 6;
        for ($i = 1; $i <= $this->currentPage; $i++) {
            $pageObjId = $objId++;
            $contentObjId = $objId++;

            $stream = $this->pages[$i];
            $streamLen = strlen($stream);

            $offsets[$pageObjId] = strlen($out);
            $out .= "$pageObjId 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . sprintf("%.2f %.2f", $this->pageWidth, $this->pageHeight) . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents $contentObjId 0 R >>\nendobj\n";

            $offsets[$contentObjId] = strlen($out);
            $out .= "$contentObjId 0 obj\n<< /Length $streamLen >>\nstream\n" . $stream . "endstream\nendobj\n";
        }

        $xrefOffset = strlen($out);
        $totalObjs = $objId;
        $out .= "xref\n0 $totalObjs\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i < $totalObjs; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $out .= "trailer\n<< /Size $totalObjs /Root 1 0 R >>\nstartxref\n$xrefOffset\n%%EOF";

        return $out;
    }

    public function output(string $filename = '', bool $inline = true): void
    {
        $binary = $this->render();
        if (empty($filename)) {
            $prefix = ($this->tipoReporte === 'admin') ? "acciones_admin_" : "reporte_registros_";
            $filename = $prefix . $this->fecha . ".pdf";
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($binary));
        $disposition = $inline ? 'inline' : 'attachment';
        header("Content-Disposition: $disposition; filename=\"$filename\"");
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $binary;
        exit;
    }
}
