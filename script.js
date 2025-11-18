document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('estimationForm');
    const resultsDisplay = document.getElementById('resultsDisplay');

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const sampleSize = parseFloat(document.getElementById('sample_size').value);
        const sampleMean = parseFloat(document.getElementById('sample_mean').value);
        const sampleStdDev = parseFloat(document.getElementById('sample_std_dev').value);
        const confidenceLevel = parseFloat(document.getElementById('confidence_level').value);

        let errors = [];
        if (isNaN(sampleSize) || sampleSize < 2) {
            errors.push("El tamaño de la muestra debe ser un número entero mayor o igual a 2.");
        }
        if (isNaN(sampleMean)) {
            errors.push("La media muestral es inválida.");
        }
        if (isNaN(sampleStdDev) || sampleStdDev < 0) {
            errors.push("La desviación estándar muestral debe ser un número no negativo.");
        }
        if (isNaN(confidenceLevel) || confidenceLevel <= 0 || confidenceLevel >= 100) {
            errors.push("El nivel de confianza debe ser un número entre 0 y 100 (exclusivos).");
        }

        if (errors.length > 0) {
            displayResults(null, errors);
        } else {
            calculateEstimations(sampleSize, sampleMean, sampleStdDev, confidenceLevel);
        }
    });

    function calculateEstimations(n, mean, stdDev, confLevel) {
        let resultsHTML = '';
        let errors = [];

        resultsHTML += `<h2><i class='fas fa-crosshairs'></i> Estimación Puntual</h2>`;
        resultsHTML += `<p>La mejor estimación puntual para la media poblacional (μ) es la media muestral (X̄).</p>`;
        resultsHTML += `<p class='highlight'><strong>Media Poblacional (μ) ≈ ${mean.toFixed(4)}</strong></p>`;

        resultsHTML += `<h2><i class='fas fa-chart-area'></i> Estimación por Intervalo (Nivel de Confianza del ${confLevel.toFixed(2)}%)</h2>`;

        const z_values = {
            90: 1.645,
            95: 1.96,
            99: 2.576,
        };

        let criticalValue = z_values[confLevel];
        let criticalValueType = 'Z';
        let warningMessage = '';

        if (!criticalValue) {
            errors.push(`No se pudo encontrar el valor crítico para el nivel de confianza especificado (${confLevel.toFixed(2)}%). Por favor, usa 90, 95 o 99.`);
        } else {
            if (n < 30) {
                criticalValueType = 'Z (aprox.)';
                warningMessage = `<p class='error-message'><strong>Nota:</strong> Para muestras pequeñas (n < 30) y desviación estándar poblacional desconocida, es más apropiado usar la distribución t-Student en lugar de la Z. Los resultados mostrados usan una aproximación Z.</p>`;
            }

            const standardError = stdDev / Math.sqrt(n);

            const marginOfError = criticalValue * standardError;

            const lowerBound = mean - marginOfError;
            const upperBound = mean + marginOfError;

            resultsHTML += `<ul>`;
            resultsHTML += `<li><strong>Error Estándar (SE):</strong> ${standardError.toFixed(4)}</li>`;
            resultsHTML += `<li><strong>Valor Crítico (${criticalValueType}):</strong> ${criticalValue.toFixed(4)}</li>`;
            resultsHTML += `<li><strong>Margen de Error (ME):</strong> ${marginOfError.toFixed(4)}</li>`;
            resultsHTML += `</ul>`;
            resultsHTML += `<p>El intervalo de confianza para la media poblacional (μ) es:</p>`;
            resultsHTML += `<p class='highlight'><strong>[${lowerBound.toFixed(4)}, ${upperBound.toFixed(4)}]</strong></p>`;
            resultsHTML += `<p>Esto significa que tenemos un ${confLevel.toFixed(2)}% de confianza de que el verdadero valor de la media poblacional se encuentra dentro de este rango.</p>`;
            resultsHTML += warningMessage;
        }

        displayResults(resultsHTML, errors);
    }

    function displayResults(resultsHTML, errors) {
        resultsDisplay.innerHTML = '';

        if (errors && errors.length > 0) {
            let errorHtml = `<p class='error-message'>Se encontraron los siguientes errores:</p><ul>`;
            errors.forEach(error => {
                errorHtml += `<li class='error-message'>${error}</li>`;
            });
            errorHtml += `</ul>`;
            resultsDisplay.innerHTML = errorHtml;
            resultsDisplay.style.display = 'block';
            resultsDisplay.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        if (resultsHTML) {
            resultsDisplay.innerHTML = resultsHTML;
            resultsDisplay.style.display = 'block';
            resultsDisplay.scrollIntoView({ behavior: 'smooth' });
        } else {
            resultsDisplay.style.display = 'none';
        }
    }
});