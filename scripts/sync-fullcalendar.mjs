/**
 * Copia FullCalendar 5.11.5 (bundle estándar + CSS + locales) a public/vendor.
 * Usar main.min.js del paquete «fullcalendar» (incluye plugins); no mezclar con @fullcalendar/core suelto.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const dest = path.join(root, 'public', 'vendor', 'fullcalendar', '5.11.5');

const copies = [
  ['node_modules/fullcalendar/main.min.css', 'main.min.css'],
  ['node_modules/@fullcalendar/bootstrap/main.min.css', 'fc-bootstrap-theme.min.css'],
  ['node_modules/fullcalendar/main.min.js', 'fc-bundle.min.js'],
  ['node_modules/fullcalendar/locales-all.min.js', 'locales-all.min.js'],
];

fs.mkdirSync(dest, { recursive: true });
for (const [relSrc, name] of copies) {
  const src = path.join(root, relSrc);
  if (!fs.existsSync(src)) {
    console.error('Falta:', relSrc, '(¿npm install?)');
    process.exit(1);
  }
  fs.copyFileSync(src, path.join(dest, name));
}

// Quitar JS antiguos (carga modular que rompía processRawCalendarOptions)
for (const legacy of [
  'fc-core.global.min.js',
  'fc-daygrid.global.min.js',
  'fc-timegrid.global.min.js',
  'fc-interaction.global.min.js',
]) {
  const p = path.join(dest, legacy);
  if (fs.existsSync(p)) {
    fs.unlinkSync(p);
  }
}
console.log('FullCalendar (bundle estándar) copiado a', dest);
