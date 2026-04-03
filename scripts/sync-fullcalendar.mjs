/**
 * Copia FullCalendar 5.11.5 desde node_modules a public/vendor (mismo origen que la app, MIME correcto).
 * Ejecutar tras: npm install
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const dest = path.join(root, 'public', 'vendor', 'fullcalendar', '5.11.5');

const copies = [
  ['node_modules/fullcalendar/main.min.css', 'main.min.css'],
  ['node_modules/@fullcalendar/core/main.global.min.js', 'fc-core.global.min.js'],
  ['node_modules/@fullcalendar/daygrid/main.global.min.js', 'fc-daygrid.global.min.js'],
  ['node_modules/@fullcalendar/timegrid/main.global.min.js', 'fc-timegrid.global.min.js'],
  ['node_modules/@fullcalendar/interaction/main.global.min.js', 'fc-interaction.global.min.js'],
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
console.log('FullCalendar copiado a', dest);
