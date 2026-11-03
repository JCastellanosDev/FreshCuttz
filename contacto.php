<?php
$tituloPagina = 'Contacto';
$paginaActual = 'contacto';
$claseBody = 'pagina-contacto';
$hojasEstiloAdicionales = ['./styles/contacto.css?v=' . filemtime(__DIR__ . '/styles/contacto.css')];
require __DIR__ . '/includes/header.php';
?>
<main class="seccion-contacto">
    <div class="tarjetas-info">
        <div class="tarjeta-mini">
            <h3>Direccion</h3>
            <a href="https://maps.app.goo.gl/LEtbziQ2he6aUzBK7" target="_blank" rel="noopener noreferrer">
                Av. Industrias, Chihuahua, Chih., 31136
            </a>

        </div>
        <div class="tarjeta-mini">
            <h3> WhatsApp</h3>
            <a href="tel:+526143633515">+52 614 363 3515</a>
        </div>
        <div class="tarjeta-mini">
            <h3> Horarios</h3>
            <p>Lun - Sab: 10:00 AM - 8:00 PM</p>
        </div>
    </div>


    <div class="tarjeta-dividida">

        <div class="contacto-formulario">
            <h2>Agenda tu corte</h2>
            <form id="formulario-contacto" method="post">
                <label for="contacto-nombre">Tu nombre</label>
                <input id="contacto-nombre" type="text" name="nombre" autocomplete="name" minlength="2" maxlength="100"
                    pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" aria-describedby="contacto-ayuda-nombre contacto-error-nombre"
                    required>
                <p class="contacto-ayuda" id="contacto-ayuda-nombre"></p>
                <p class="contacto-error" id="contacto-error-nombre" aria-live="polite"></p>
                <label for="contacto-telefono">Tu celular</label>
                <input id="contacto-telefono" type="tel" name="telefono" autocomplete="tel-national" inputmode="numeric"
                    pattern="[0-9]{10}" aria-describedby="contacto-ayuda-telefono contacto-error-telefono" required>
                <p class="contacto-ayuda" id="contacto-ayuda-telefono">Exactamente 10 números</p>
                <p class="contacto-error" id="contacto-error-telefono" aria-live="polite"></p>
                <label for="contacto-servicio">Servicio</label>
                <select id="contacto-servicio" name="servicio" aria-describedby="contacto-error-servicio" required>
                    <option value="" disabled selected>Elige tu servicio...</option>
                    <option value="fade">Corte Fade Navaja</option>
                    <option value="clasico">Clasico Tradicional</option>
                    <option value="barba">Diseño de Barba</option>
                </select>
                <p class="contacto-error" id="contacto-error-servicio" aria-live="polite"></p>
                <button type="submit">Continuar en WhatsApp</button>
                <p class="contacto-ayuda">Se abrirá WhatsApp con tu solicitud. Revisa y envía el mensaje para consultar
                    disponibilidad.</p>
            </form>
        </div>


        <div class="contacto-mapa">

            <iframe title="Ubicacion de Fresh Cuttz Barbershop"
                src="https://maps.google.com/maps?q=28.726003,-106.111288&amp;z=16&amp;output=embed" width="100%"
                height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>
</main>
<script src="./scripts/contacto.js?v=<?= filemtime(__DIR__ . '/scripts/contacto.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>