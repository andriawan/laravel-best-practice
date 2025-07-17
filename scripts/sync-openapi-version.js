#!/usr/bin/env node
/**
 * scripts/sync-openapi-version.js
 * Reads version from <root>/version.json
 * and writes it into <root>/resources/swagger/openapi.json
 */

import { existsSync, readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

/* ------------------------------------------------------------------ */
/* 1. Read version.json (one directory above this script)             */
/* ------------------------------------------------------------------ */
const versionFile = join(__dirname, '..', 'version.json');
if (!existsSync(versionFile)) {
  console.error('❌  version.json not found in project root.');
  process.exit(1);
}

const { version } = JSON.parse(readFileSync(versionFile, 'utf8'));
if (!version) {
  console.error('❌  "version" field missing in version.json.');
  process.exit(1);
}

/* ------------------------------------------------------------------ */
/* 2. Update openapi.json                                             */
/* ------------------------------------------------------------------ */
const openApiFile = join(__dirname, '..', 'resources', 'swagger', 'openapi.json');
if (!existsSync(openApiFile)) {
  console.error('❌  openapi.json not found at expected path.');
  process.exit(1);
}

const openApi = JSON.parse(readFileSync(openApiFile, 'utf8'));
const oldVersion = openApi.info.version;
openApi.info.version = version;

writeFileSync(openApiFile, JSON.stringify(openApi, null, 2) + '\n', 'utf8');

console.log(`✅  Synced OpenAPI version: ${oldVersion} → ${version}`);