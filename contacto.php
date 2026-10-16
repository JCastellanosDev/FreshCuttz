<?php
$tituloPagina = 'Contacto';
$paginaActual = 'contacto';
$claseBody = 'pagina-contacto';
$hojasEstiloAdicionales = ['./styles/contacto.css'];
require __DIR__ . '/includes/header.php';
?>
   <main class="seccion-contacto">
    <div class="tarjetas-info">
        <div class="tarjeta-mini">
            <h3>Direccion</h3>
            <a href="https://maps.app.goo.gl/LEtbziQ2he6aUzBK7"target="_blank" rel="noopener noreferrer">
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
            <form action="#">
                <input type="text" aria-label="Tu nombre" placeholder="Tu Nombre" required>
                <input type="tel" aria-label="Tu celular" placeholder="Tu Celular" required>
                <select aria-label="Servicio" required>
                    <option value="" disabled selected>Elige tu servicio...</option>
                    <option value="fade">Corte Fade Navaja</option>
                    <option value="clasico">Clasico Tradicional</option>
                    <option value="barba">Diseño de Barba</option>
                </select>
                <button type="submit">Enviar Mensaje</button>
            </form>
        </div>
        
        
        <div class="contacto-mapa">
          
            <iframe title="Ubicacion de Fresh Cuttz Barbershop" src="https://maps.google.com/maps?q=28.726003,-106.111288&amp;z=16&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>