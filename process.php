<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de la Estimación</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1 class="title"><i class="fas fa-chart-line"></i> Resultados de la Estimación</h1>
            <p class="subtitle">Aquí están los resultados de tus cálculos estadísticos.</p>
        </header>

        <div class="card results-area">
            <?php
            function getCriticalValue($confidence_level, $sample_size = null) {
                $z_values = [
                    90 => 1.645,
                    95 => 1.96,
                    99 => 2.576,
                ];

                if (isset($z_values[$confidence_level])) {
                    if ($sample_size >= 30) {
                        return ['value' => $z_values[$confidence_level], 'type' => 'Z'];
                    } else {
                        return ['value' => $z_values[$confidence_level], 'type' => 'Z (aprox.)'];
                    }
                } else {
                    return ['value' => null, 'type' => 'N/A'];
                }
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $sample_size = filter_input(INPUT_POST, 'sample_size', FILTER_VALIDATE_INT);
                $sample_mean = filter_input(INPUT_POST, 'sample_mean', FILTER_VALIDATE_FLOAT);
                $sample_std_dev = filter_input(INPUT_POST, 'sample_std_dev', FILTER_VALIDATE_FLOAT);
                $confidence_level = filter_input(INPUT_POST, 'confidence_level', FILTER_VALIDATE_FLOAT);

                $errors = [];

                if ($sample_size === false || $sample_size < 2) {
                    $errors[] = "El tamaño de la muestra debe ser un número entero mayor o igual a 2.";
                }
                if ($sample_mean === false) {
                    $errors[] = "La media muestral es inválida.";
                }
                if ($sample_std_dev === false || $sample_std_dev < 0) {
                    $errors[] = "La desviación estándar muestral debe ser un número no negativo.";
                }
                if ($confidence_level === false || $confidence_level <= 0 || $confidence_level >= 100) {
                    $errors[] = "El nivel de confianza debe ser un número entre 0 y 100 (exclusivos).";
                }

                if (empty($errors)) {
                    // Estimación Puntual
                    echo "<h2><i class='fas fa-crosshairs'></i> Estimación Puntual</h2>";
                    echo "<p>La mejor estimación puntual para la media poblacional (μ) es la media muestral (X̄).</p>";
                    echo "<p class='highlight'><strong>Media Poblacional (μ) ≈ " . number_format($sample_mean, 4) . "</strong></p>";

                    // Estimación por Intervalo
                    echo "<h2><i class='fas fa-chart-area'></i> Estimación por Intervalo (Nivel de Confianza del " . number_format($confidence_level, 2) . "%)</h2>";

                    $critical_value_data = getCriticalValue($confidence_level, $sample_size);
                    $critical_value = $critical_value_data['value'];
                    $critical_value_type = $critical_value_data['type'];


                    if ($critical_value) {
                        // Error estándar de la media
                        $standard_error = $sample_std_dev / sqrt($sample_size);

                        // Margen de error
                        $margin_of_error = $critical_value * $standard_error;

                        // Intervalo de confianza
                        $lower_bound = $sample_mean - $margin_of_error;
                        $upper_bound = $sample_mean + $margin_of_error;

                        echo "<ul>";
                        echo "<li><strong>Error Estándar (SE):</strong> " . number_format($standard_error, 4) . "</li>";
                        echo "<li><strong>Valor Crítico (" . $critical_value_type . "):</strong> " . number_format($critical_value, 4) . "</li>";
                        echo "<li><strong>Margen de Error (ME):</strong> " . number_format($margin_of_error, 4) . "</li>";
                        echo "</ul>";
                        echo "<p>El intervalo de confianza para la media poblacional (μ) es:</p>";
                        echo "<p class='highlight'><strong>[" . number_format($lower_bound, 4) . ", " . number_format($upper_bound, 4) . "]</strong></p>";
                        echo "<p>Esto significa que tenemos un " . number_format($confidence_level, 2) . "% de confianza de que el verdadero valor de la media poblacional se encuentra dentro de este rango.</p>";

                        if ($critical_value_type === 'Z (aprox.)') {
                            echo "<p class='error-message'><strong>Nota:</strong> Para muestras pequeñas (n < 30) y desviación estándar poblacional desconocida, es más apropiado usar la distribución t-Student en lugar de la Z. Los resultados mostrados usan una aproximación Z.</p>";
                        }

                    } else {
                        echo "<p class='error-message'>No se pudo encontrar el valor crítico para el nivel de confianza especificado (" . number_format($confidence_level, 2) . "%). Por favor, usa 90, 95 o 99.</p>";
                    }
                } else {
                    echo "<p class='error-message'>Se encontraron los siguientes errores:</p>";
                    echo "<ul>";
                    foreach ($errors as $error) {
                        echo "<li class='error-message'>" . $error . "</li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "<p class='error-message'>Acceso denegado. Por favor, envía el formulario desde la página principal.</p>";
            }
            ?>
        </div>
        <div class="footer">
            <p><a href="index.html" class="button-link"><i class="fas fa-arrow-left"></i> Volver a la herramienta de estimación</a></p>
        </div>
    </div>
</body>
</html>