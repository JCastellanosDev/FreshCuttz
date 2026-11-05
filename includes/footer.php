    <footer class="pie">
        <div class="contenedor">
            <div class="pie-lema">
                <p><span>#</span>ISTAYFRESH</p>
                <img src="./image/logo1.webp" width="259" height="192" alt="Fresh Cuttz Barbershop" loading="lazy">
            </div>

            <div class="pie-columnas">
                <section aria-labelledby="pie-visitanos">
                    <h2 id="pie-visitanos">Visítanos</h2>
                    <ul class="pie-lista">
                        <li>
                            <?= svgIcono('ubicacion') ?>
                            <a class="pie-enlace" href="https://maps.app.goo.gl/LEtbziQ2he6aUzBK7" target="_blank"
                                rel="noopener">Av. Industrias, Chihuahua, Chih., 31136<?= svgIcono('externo', 'icono-sm') ?><span class="sr-only"> (abre Google Maps)</span></a>
                        </li>
                        <li>
                            <?= svgIcono('reloj') ?>
                            <span>Lun – Sáb · 10:00 – 20:00</span>
                        </li>
                    </ul>
                </section>

                <section aria-labelledby="pie-contacto">
                    <h2 id="pie-contacto">Agenda o pregunta</h2>
                    <ul class="pie-lista">
                        <li>
                            <?= svgIcono('mensaje') ?>
                            <a class="pie-enlace" href="https://wa.me/526143633515" target="_blank"
                                rel="noopener">WhatsApp +52 614 363 3515<span class="sr-only"> (abre WhatsApp)</span></a>
                        </li>
                        <li>
                            <?= svgIcono('telefono') ?>
                            <a class="pie-enlace" href="tel:+526143633515">Llamar a la barbería</a>
                        </li>
                    </ul>
                </section>

                <section class="pie-redes" aria-labelledby="pie-redes">
                    <h2 id="pie-redes">Síguenos</h2>
                    <a class="btn btn-contorno" href="https://www.facebook.com/FreshCuttz" target="_blank"
                        rel="noopener"><?= svgIcono('facebook') ?>Facebook<span class="sr-only"> (abre en otra pestaña)</span></a>
                </section>
            </div>

            <div class="pie-legal">
                <span>&copy; <?= date('Y') ?> Fresh Cuttz Barbershop · Chihuahua, México</span>
                <span>Est. 2013</span>
            </div>
        </div>
    </footer>
</body>
</html>
