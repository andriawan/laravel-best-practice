#!/usr/bin/env node
/**
 * scripts/sync-openapi-version.js
 * Reads version from <root>/version.json
 * and writes it into <root>/resources/swagger/openapi.json
 */

import { existsSync, readFileSync, writeFileSync } from 'fs';

const versionPath = new URL('../version.json', import.meta.url);
const openApiPath = new URL('../resources/swagger/openapi.json', import.meta.url);


if (!existsSync(versionPath)) {
  console.error('❌  version.json not found in project root.');
  process.exit(1);
}

const { version } = JSON.parse(readFileSync(versionPath, 'utf8'));
if (!version) {
  console.error('❌  "version" field missing in version.json.');
  process.exit(1);
}

if (!existsSync(openApiPath)) {
  console.error('❌  openapi.json not found at expected path.');
  process.exit(1);
}

const openApi = JSON.parse(readFileSync(openApiPath, 'utf8'));
const oldVersion = openApi.info.version;
openApi.info.version = version;

writeFileSync(openApiPath, JSON.stringify(openApi, null, 2) + '\n', 'utf8');

console.log(`✅ Synced OpenAPI version: ${oldVersion} → ${version}`);