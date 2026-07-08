import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const alpineSource = join(root, 'node_modules/alpinejs/dist/cdn.min.js');
const alpineTargetDir = join(root, 'public/vendor/alpine');
const alpineTarget = join(alpineTargetDir, 'alpine.min.js');

mkdirSync(alpineTargetDir, { recursive: true });
copyFileSync(alpineSource, alpineTarget);

console.log('Copied Alpine.js to public/vendor/alpine/alpine.min.js');
