<?php
$tituloPagina = 'Contacto';
$paginaActual = 'contacto';
$claseBody = 'pagina-contacto';
$hojasEstiloAdicionales = ['./styles/contacto.css?v=' . filemtime(__DIR__ . '/styles/contacto.css')];
require __DIR__ . '/includes/header.php';
?>
<main id="contenido" class="contacto contenedor">
    <div class="contacto-info">
        <header class="contacto-cabecera">
            <h1>Pasa a la silla</h1>
            <p class="contacto-intro">Estamos sobre Av. Industrias, en Chihuahua. Agenda por WhatsApp y llega directo a tu corte.</p>
        </header>

        <section class="contacto-datos" aria-labelledby="contacto-datos-titulo">
            <h2 id="contacto-datos-titulo" class="sr-only">Datos de contacto</h2>
            <address>
                <dl class="datos-lista">
                    <div class="dato">
                        <dt><?= svgIcono('ubicacion') ?>Dirección</dt>
                        <dd>
                            <p class="dato-valor">Av. Industrias, Chihuahua, Chih., 31136</p>
                            <a class="btn btn-contorno dato-accion" href="https://maps.app.goo.gl/LEtbziQ2he6aUzBK7" target="_blank" rel="noopener noreferrer">
                                Cómo llegar<?= svgIcono('externo', 'icono-sm') ?><span class="sr-only"> (se abre en Google Maps)</span>
                            </a>
                        </dd>
                    </div>
                    <div class="dato">
                        <dt><?= svgIcono('mensaje') ?>WhatsApp</dt>
                        <dd>
                            <a class="dato-valor dato-telefono t-num" href="tel:+526143633515">+52 614 363 3515</a>
                            <a class="btn btn-contorno dato-accion" href="https://wa.me/526143633515" target="_blank" rel="noopener noreferrer">
                                Escribir por WhatsApp<?= svgIcono('externo', 'icono-sm') ?><span class="sr-only"> (se abre en otra pestaña)</span>
                            </a>
                        </dd>
                    </div>
                    <div class="dato">
                        <dt><?= svgIcono('reloj') ?>Horario</dt>
                        <dd>
                            <p class="dato-valor t-num">Lun – Sáb · 10:00 – 20:00</p>
                            <p class="dato-nota">Domingo cerrado</p>
                            <span class="insignia dato-estado" id="contacto-estado" hidden></span>
                        </dd>
                    </div>
                </dl>
            </address>
        </section>
    </div>

    <section class="contacto-agenda tarjeta" aria-labelledby="contacto-agenda-titulo">
        <h2 id="contacto-agenda-titulo">Agenda tu corte</h2>
        <p class="agenda-intro">Déjanos tus datos y te abrimos WhatsApp con el mensaje listo. Solo revísalo y envíalo para confirmar disponibilidad.</p>
        <form id="formulario-contacto" class="agenda-form" method="post" novalidate>
            <div class="campo">
                <label for="contacto-nombre">Tu nombre</label>
                <input id="contacto-nombre" type="text" name="nombre" autocomplete="name" minlength="2" maxlength="100"
                    pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" aria-describedby="contacto-error-nombre" required>
                <p class="campo-error" id="contacto-error-nombre" aria-live="polite"></p>
            </div>
            <div class="campo">
                <label for="contacto-telefono">Tu celular</label>
                <input id="contacto-telefono" type="tel" name="telefono" autocomplete="tel-national" inputmode="numeric"
                    pattern="[0-9]{10}" placeholder="6140000000"
                    aria-describedby="contacto-ayuda-telefono contacto-error-telefono" required>
                <p class="campo-ayuda" id="contacto-ayuda-telefono">10 dígitos, sin lada internacional.</p>
                <p class="campo-error" id="contacto-error-telefono" aria-live="polite"></p>
            </div>
            <div class="campo campo-ancho">
                <label for="contacto-servicio">Servicio</label>
                <select id="contacto-servicio" name="servicio" aria-describedby="contacto-error-servicio" required>
                    <option value="" disabled selected>Elige tu servicio</option>
                    <option value="fade">Corte Fade Navaja</option>
                    <option value="clasico">Clásico Tradicional</option>
                    <option value="barba">Diseño de Barba</option>
                </select>
                <p class="campo-error" id="contacto-error-servicio" aria-live="polite"></p>
            </div>
            <div class="agenda-envio campo-ancho">
                <button type="submit" class="btn btn-primario btn-lg agenda-boton">
                    <?= svgIcono('mensaje') ?>Continuar en WhatsApp
                </button>
            </div>
        </form>
    </section>

    <figure class="contacto-mapa">
        <iframe title="Mapa: ubicación de Fresh Cuttz Barbershop en Av. Industrias, Chihuahua"
            src="https://maps.google.com/maps?q=28.726003,-106.111288&amp;z=16&amp;output=embed"
            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </figure>
</main>
<script src="./scripts/contacto.js?v=<?= filemtime(__DIR__ . '/scripts/contacto.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
